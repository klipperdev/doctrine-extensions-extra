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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensionsExtra\Model\Traits\TranslatableInterface;

/**
 * Interface for translatable entity repository.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TranslatableRepositoryInterface
{
    /**
     * Find one translated by id.
     *
     * @param int|string  $id     The id
     * @param null|string $locale The locale
     */
    public function findOneTranslatedById($id, ?string $locale = null): TranslatableInterface;

    /**
     * Find one translated by criteria.
     *
     * @param array       $criteria The criteria
     * @param null|string $locale   The locale
     */
    public function findOneTranslatedBy(array $criteria, ?string $locale = null): TranslatableInterface;

    /**
     * Find translated by criteria.
     *
     * @param array       $criteria The criteria
     * @param null|string $locale   The locale
     *
     * @return TranslatableInterface[]
     */
    public function findTranslatedBy(array $criteria, ?string $locale = null): array;

    /**
     * Returns translated one (or null if not found) result for given locale.
     *
     * @param Query|QueryBuilder $query         A Doctrine query builder instance
     * @param null|string        $locale        A locale name
     * @param int                $hydrationMode A Doctrine results hydration mode
     *
     * @return mixed
     */
    public function getOneOrNullResult($query, ?string $locale = null, ?int $hydrationMode = null);

    /**
     * Returns translated results for given locale.
     *
     * @param Query|QueryBuilder $query         A Doctrine query builder instance
     * @param null|string        $locale        A locale name
     * @param int                $hydrationMode A Doctrine results hydration mode
     */
    public function getResult($query, ?string $locale = null, int $hydrationMode = AbstractQuery::HYDRATE_OBJECT): array;

    /**
     * Returns translated array results for given locale.
     *
     * @param Query|QueryBuilder $query  A Doctrine query builder instance
     * @param null|string        $locale A locale name
     */
    public function getArrayResult($query, ?string $locale = null): array;

    /**
     * Returns translated single result for given locale.
     *
     * @param Query|QueryBuilder $query         A Doctrine query builder instance
     * @param null|string        $locale        A locale name
     * @param null|int           $hydrationMode A Doctrine results hydration mode
     *
     * @return mixed
     */
    public function getSingleResult($query, ?string $locale = null, ?int $hydrationMode = null);

    /**
     * Returns translated scalar result for given locale.
     *
     * @param Query|QueryBuilder $query  A Doctrine query builder instance
     * @param null|string        $locale A locale name
     */
    public function getScalarResult($query, ?string $locale = null): array;

    /**
     * Returns translated single scalar result for given locale.
     *
     * @param Query|QueryBuilder $query  A Doctrine query builder instance
     * @param null|string        $locale A locale name
     *
     * @return mixed
     */
    public function getSingleScalarResult($query, ?string $locale = null);
}
