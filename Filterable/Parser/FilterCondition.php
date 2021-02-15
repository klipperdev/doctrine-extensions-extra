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

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterCondition implements FilterInterface
{
    private string $condition;

    /**
     * @var FilterCondition[]|FilterRule[]
     */
    private array $rules;

    /**
     * @param FilterCondition[]|FilterRule[] $rules
     */
    public function __construct(string $condition, array $rules = [])
    {
        $this->condition = $condition;
        $this->rules = $rules;
    }

    /**
     * @param FilterCondition[]|FilterRule[] $rules
     */
    public static function create(string $condition, array $rules = []): self
    {
        return new self($condition, $rules);
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    /**
     * @return FilterCondition[]|FilterRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param FilterCondition|FilterRule $rule
     */
    public function addRule($rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'condition' => $this->condition,
            'rules' => array_map(static function ($rule) {
                return $rule->toArray();
            }, $this->rules),
        ];
    }
}
