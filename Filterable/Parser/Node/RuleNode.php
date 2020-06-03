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

/**
 * Rule node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class RuleNode extends Node
{
    private string $field;

    /**
     * @var null|bool|bool[]|float|float[]|int|int[]|string|string[]
     */
    private $value;

    /**
     * @var null|mixed
     */
    private $queryValue;

    /**
     * @param string $field The field name
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function getName(): string
    {
        return $this->field;
    }

    /**
     * Get the field.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Check if the rule is collectible.
     */
    public function isCollectible(): bool
    {
        return false;
    }

    /**
     * Get the locked size for the collection.
     */
    public function getSizeCollection(): int
    {
        return 0;
    }

    /**
     * Check if the value is required.
     */
    public function isRequiredValue(): bool
    {
        return true;
    }

    /**
     * Get the operator.
     */
    abstract public function getOperator(): string;

    /**
     * Set the value.
     *
     * @param null|bool|bool[]|float|float[]|int|int[]|string|string[] $value The value
     *
     * @return static
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value.
     *
     * @return null|bool|bool[]|float|float[]|int|int[]|string|string[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value for query parameter.
     *
     * @param null|mixed $value The value for query parameter
     *
     * @return static
     */
    public function setQueryValue($value): self
    {
        $this->queryValue = $value;

        return $this;
    }

    /**
     * Get the value for query parameter.
     *
     * @return null|mixed
     */
    public function getQueryValue()
    {
        return $this->queryValue ?? $this->value;
    }
}
