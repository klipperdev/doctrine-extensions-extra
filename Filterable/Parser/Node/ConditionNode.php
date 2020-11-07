<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers\NodeTransformerInterface;

/**
 * Condition node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class ConditionNode extends Node
{
    /**
     * @var NodeInterface[]
     */
    private array $rules = [];

    /**
     * @param NodeInterface[] $rules The rules
     */
    public function __construct(array $rules = [])
    {
        $this->setRules($rules);
    }

    public function getName(): string
    {
        return $this->getCondition();
    }

    /**
     * Get the condition.
     */
    abstract public function getCondition(): string;

    /**
     * Set the rules.
     *
     * @param NodeInterface[] The rules
     *
     * @return static
     */
    public function setRules(array $rules): self
    {
        $this->rules = [];

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Add the rule.
     *
     * @param NodeInterface $rule The rule
     *
     * @return static
     */
    public function addRule(NodeInterface $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Get the rules.
     *
     * @return NodeInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function compile(CompileArgs $arguments): string
    {
        $str = '';

        foreach ($this->getRules() as $i => $rule) {
            $subStr = $rule instanceof RuleNode && ($rqv = $rule->getQueryValue()) instanceof NodeTransformerInterface
                ? $rqv->compile($arguments, $rule) : $rule->compile($arguments);

            if (!empty($subStr)) {
                $str .= ('' === $str ? '(' : ' '.$this->getCondition().' (').$subStr.')';
            }
        }

        return $str;
    }
}
