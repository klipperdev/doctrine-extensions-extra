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

use Doctrine\ORM\Query;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface FilterableQueryInterface
{
    public const VALIDATE_ALL = 3;

    public const VALIDATE_VALUE = 2;

    public const VALIDATE_NODE = 1;

    public const VALIDATE_NONE = 0;

    /**
     * @throw ObjectMetadataNotFoundException When the metadata is not found
     */
    public function validate(string $metadataName, array $filter, bool $forceFirstCondition = false): NodeInterface;

    /**
     * Filter the query.
     *
     * @param Query                    $query    The query
     * @param null|array|NodeInterface $filter   The filter
     * @param int                      $validate Check if filter must be validate for nodes only or nodes and values
     */
    public function filter(Query $query, $filter, int $validate = self::VALIDATE_NONE): Query;
}
