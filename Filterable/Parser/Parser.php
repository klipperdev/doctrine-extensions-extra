<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidConditionTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidJsonException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidNodeTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidRuleTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\RequireParameterException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\UnexpectedTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\AndNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ConditionNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;

/**
 * Parser of filters.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Parser
{
    /**
     * @var array
     */
    private $nodeConditions = [];

    /**
     * @var array
     */
    private $nodeRules = [];

    /**
     * @var array
     */
    private $mapRules = [];

    /**
     * @var array
     */
    private $mapForms = [];

    /**
     * Constructor.
     *
     * @param null|array $nodeConditions The config of node conditions
     * @param null|array $nodeRules      The config of node rules
     * @param null|array $mapRules       The config of map rules
     * @param null|array $mapForms       The config of map forms
     */
    public function __construct(
        ?array $nodeConditions = null,
        ?array $nodeRules = null,
        ?array $mapRules = null,
        ?array $mapForms = null
    ) {
        $this->setNodeConditions(\is_array($nodeConditions) ? $nodeConditions : ParserConfig::getDefaultNodeConditions());
        $this->setNodeRules(\is_array($nodeRules) ? $nodeRules : ParserConfig::getDefaultNodeRules());
        $this->setMapRules(\is_array($mapRules) ? $mapRules : ParserConfig::getDefaultMapRules());
        $this->setMapForms(\is_array($mapForms) ? $mapForms : ParserConfig::getDefaultMapForms());
    }

    /**
     * Set the node conditions.
     *
     * @param array $nodeConditions The map of node conditions
     *
     * @return static
     */
    public function setNodeConditions(array $nodeConditions): self
    {
        $this->nodeConditions = [];

        foreach ($nodeConditions as $name => $nodeClass) {
            $this->addNodeCondition($name, $nodeClass);
        }

        return $this;
    }

    /**
     * Add the node condition.
     *
     * @param string $name      The condition name
     * @param string $nodeClass The node condition class
     *
     * @return static
     */
    public function addNodeCondition(string $name, string $nodeClass): self
    {
        $this->nodeConditions[$name] = $nodeClass;

        return $this;
    }

    /**
     * Get the node conditions.
     */
    public function getNodeConditions(): array
    {
        return $this->nodeConditions;
    }

    /**
     * Get the node condition.
     *
     * @param string $name The condition name
     *
     * @return null|string The class name of node condition
     */
    public function getNodeCondition(string $name): ?string
    {
        return $this->nodeConditions[$name] ?? null;
    }

    /**
     * Set the node rules.
     *
     * @param array $nodeRules The map of rules
     *
     * @return static
     */
    public function setNodeRules(array $nodeRules): self
    {
        $this->nodeRules = [];

        foreach ($nodeRules as $name => $nodeClass) {
            $this->addNodeRule($name, $nodeClass);
        }

        return $this;
    }

    /**
     * Add the node rule.
     *
     * @param string $name      The rule name
     * @param string $nodeClass The node rule class
     *
     * @return static
     */
    public function addNodeRule(string $name, string $nodeClass): self
    {
        $this->nodeRules[$name] = $nodeClass;

        return $this;
    }

    /**
     * Get the node rules.
     */
    public function getNodeRules(): array
    {
        return $this->nodeRules;
    }

    /**
     * Get the node rule.
     *
     * @param string $name The rule name
     *
     * @return null|string The class name of node rule
     */
    public function getNodeRule(string $name): ?string
    {
        return $this->nodeRules[$name] ?? null;
    }

    /**
     * Set the map rules.
     *
     * @param array $mapRules The map of rules
     *
     * @return static
     */
    public function setMapRules(array $mapRules): self
    {
        $this->mapRules = [];

        foreach ($mapRules as $type => $names) {
            $this->addMapRule($type, $names);
        }

        return $this;
    }

    /**
     * Add the node rule.
     *
     * @param string   $type  The type of metadata
     * @param string[] $names The rule names
     *
     * @return static
     */
    public function addMapRule(string $type, array $names): self
    {
        $previous = $this->mapRules[$type] ?? [];
        $this->mapRules[$type] = array_unique(array_merge($previous, array_values($names)));

        return $this;
    }

    /**
     * Get the map rules.
     */
    public function getMapRules(): array
    {
        return $this->mapRules;
    }

    /**
     * Get the map rule.
     *
     * @param string $type The type of metadata
     *
     * @return null|string[] The rule names
     */
    public function getMapRule(string $type): ?array
    {
        return $this->mapRules[$type] ?? null;
    }

    /**
     * Set the map forms.
     *
     * @param array $mapForms The map forms
     *
     * @return static
     */
    public function setMapForms(array $mapForms): self
    {
        $this->mapForms = [];

        foreach ($mapForms as $type => $formClass) {
            $this->addMapForm($type, $formClass);
        }

        return $this;
    }

    /**
     * Add the map form.
     *
     * @param string       $type      The type of metadata
     * @param array|string $formClass The form class or the array of form class and form options
     *
     * @return static
     */
    public function addMapForm(string $type, $formClass): self
    {
        $this->mapForms[$type] = $formClass;

        return $this;
    }

    /**
     * Get the map forms.
     *
     * @return array The map of form class or array of form class and form options
     */
    public function getMapForms(): array
    {
        return $this->mapForms;
    }

    /**
     * Get the map form.
     *
     * @param string $type The type of metadata
     *
     * @return null|array|string The form class or the array of form class and form options
     */
    public function getMapForm(string $type)
    {
        return $this->mapForms[$type] ?? null;
    }

    /**
     * Parse the query filter.
     *
     * @param string $json                The json
     * @param bool   $forceFirstCondition Check if the condition node must be added in root
     */
    public function parse(string $json, bool $forceFirstCondition = true): NodeInterface
    {
        $value = json_decode($json, true);

        if (json_last_error()) {
            throw new InvalidJsonException(json_last_error_msg());
        }

        $node = $this->parseNode($value);

        if ($forceFirstCondition && !$node instanceof ConditionNode) {
            $node = new AndNode([$node]);
        }

        return $node;
    }

    /**
     * Parse the node.
     *
     * @param array       $node   The node
     * @param null|string $parent The parent path node
     */
    private function parseNode(array $node, ?string $parent = null): NodeInterface
    {
        if (!\is_array($node)) {
            throw new InvalidNodeTypeException($parent);
        }

        if (isset($node['condition'])) {
            return $this->buildCondition($this->validateCondition($node, $parent), $parent);
        }

        return $this->buildRule($this->validateRule($node, $parent));
    }

    /**
     * Validate the node.
     *
     * @param array       $node   The node
     * @param null|string $parent The path of parent
     */
    private function validateCondition(array $node, ?string $parent = null): array
    {
        if (null === $this->getNodeCondition($node['condition'])) {
            throw new InvalidConditionTypeException($this->getNodeConditions(), $node['condition'], $parent);
        }

        $path = $this->getPath($node['condition'], $parent, true);

        if (!isset($node['rules'])) {
            throw new RequireParameterException('rules', $path);
        }

        if (!\is_array($node['rules'])) {
            throw new UnexpectedTypeException(['array'], \gettype($node['rules']), $path);
        }

        return $node;
    }

    /**
     * Build the condition node.
     *
     * @param array       $node   The node
     * @param null|string $parent The path of parent
     */
    private function buildCondition(array $node, ?string $parent = null): ConditionNode
    {
        $path = $this->getPath($node['condition'], $parent, true);
        $class = $this->getNodeCondition($node['condition']);
        /** @var ConditionNode $condition */
        $condition = new $class();

        foreach ($node['rules'] as $key => $rule) {
            $condition->addRule($this->parseNode($rule, $this->getPath($key, $path)));
        }

        return $condition;
    }

    /**
     * Build the rule node.
     *
     * @param array $node The node
     */
    private function buildRule(array $node): RuleNode
    {
        $class = $this->getNodeRule($node['operator']);
        /** @var RuleNode $rule */
        $rule = new $class($node['field']);
        $rule->setValue($node['value']);

        return $rule;
    }

    /**
     * Validate the rule node.
     *
     * @param array  $node   The node
     * @param string $parent The parent
     */
    private function validateRule(array $node, ?string $parent = null): array
    {
        $path = isset($node['field']) ? $this->getPath($node['field'], $parent, true) : $parent;

        if (!isset($node['field'])) {
            throw new RequireParameterException('field', $path);
        }
        if (!\is_string($node['field'])) {
            throw new UnexpectedTypeException(['string'], \gettype($node['field']), $path);
        }

        if (!isset($node['operator'])) {
            $node['operator'] = 'equal';
        } elseif (!\is_string($node['operator'])) {
            throw new UnexpectedTypeException(['string'], \gettype($node['operator']), $this->getPath('operator', $path));
        }

        if (null === $this->getNodeRule($node['operator'])) {
            throw new InvalidRuleTypeException($this->getNodeRules(), $node['operator'], $path);
        }

        if (!isset($node['value'])) {
            $node['value'] = null;
        } else {
            $this->validateRuleValue($node['value'], $this->getPath('value', $path));
        }

        return $node;
    }

    /**
     * Validate the value of rule.
     *
     * @param mixed  $value The value of rule
     * @param string $path  The path
     */
    private function validateRuleValue($value, string $path): void
    {
        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                $this->validateRuleValue($val, $this->getPath($key, $path));
            }

            return;
        }

        if (null !== $value && !\is_string($value) && !\is_bool($value)
                && !\is_int($value) && !\is_float($value)) {
            $expectedTypes = ['string', 'boolean', 'integer', 'float', 'long', 'null'];

            throw new UnexpectedTypeException($expectedTypes, \gettype($value), $path);
        }
    }

    /**
     * Get the current path.
     *
     * @param int|string  $name    The name of current node
     * @param null|string $parent  The path of parent
     * @param bool        $bracket Check if the bracket must be used
     */
    private function getPath($name, ?string $parent = null, bool $bracket = false): string
    {
        $prefix = '.';
        $suffix = '';

        if ($bracket) {
            $prefix = '[';
            $suffix = ']';
        }

        return trim($parent.$prefix.$name.$suffix, '.');
    }
}
