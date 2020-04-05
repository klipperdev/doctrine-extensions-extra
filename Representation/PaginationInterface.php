<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Representation;

/**
 * Interface for pagination.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface PaginationInterface
{
    /**
     * Get the page number.
     */
    public function getPage(): int;

    /**
     * Get the limit of pagination.
     */
    public function getLimit(): int;

    /**
     * Get the number of pages.
     */
    public function getPages(): int;

    /**
     * Get the size of the collection.
     */
    public function getTotal(): int;

    /**
     * Get the results.
     *
     * @return object[]
     */
    public function getResults(): array;
}
