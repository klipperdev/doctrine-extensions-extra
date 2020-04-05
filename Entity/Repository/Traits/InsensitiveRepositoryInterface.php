<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits;

/**
 * Interface for insensitive entity repository.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface InsensitiveRepositoryInterface
{
    /**
     * Finds entities by a set of criteria with insensitive field.
     *
     * @return array The objects
     */
    public function findByInsensitive(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}
