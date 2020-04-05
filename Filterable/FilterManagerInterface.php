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
interface FilterManagerInterface
{
    /**
     * Get the config of filters.
     *
     * @param string $class The class name
     */
    public function getFilters(string $class): FilterConfig;

    /**
     * Get the config of filters by object name.
     *
     * @param string $name The object name
     */
    public function getFiltersByName(string $name): FilterConfig;
}
