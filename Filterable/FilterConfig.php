<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterConfig
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $groups;

    /**
     * Constructor.
     *
     * @param array $filters The filters
     * @param array $groups  The groups
     */
    public function __construct(array $filters, array $groups)
    {
        $this->filters = $filters;
        $this->groups = $groups;
    }

    /**
     * Get the filters.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get the groups.
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
