<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Searchable;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SearchableFields
{
    /**
     * @var string[]
     */
    private array $fields;

    private array $joins;

    /**
     * @param string[] $fields
     */
    public function __construct(array $fields, array $joins)
    {
        $this->fields = $fields;
        $this->joins = $joins;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getJoins(): array
    {
        return $this->joins;
    }
}
