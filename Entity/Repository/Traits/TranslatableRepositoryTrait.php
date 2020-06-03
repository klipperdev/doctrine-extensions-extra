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

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensionsExtra\Model\Traits\TranslatableInterface;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;

/**
 * Insensitive trait for Doctrine\ORM\EntityRepository.
 *
 * @method QueryBuilder createQueryBuilder($alias, $indexBy = null)
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait TranslatableRepositoryTrait
{
    /**
     * @param mixed $id
     */
    public function findOneTranslatedById($id, ?string $locale = null): TranslatableInterface
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameter('id', $id, \is_string($id) ? Types::GUID : null)
        ;

        return $this->getSingleResult($qb, $locale);
    }

    public function findOneTranslatedBy(array $criteria, ?string $locale = null): TranslatableInterface
    {
        $qb = $this->createQueryBuilder('t');

        foreach ($criteria as $key => $value) {
            $qb
                ->andWhere('t.'.$key.' = :'.$key)
                ->setParameter($key, $value, 'id' === $key && \is_string($value) ? Type::GUID : null)
            ;
        }

        return $this->getSingleResult($qb, $locale);
    }

    public function findTranslatedBy(array $criteria, ?string $locale = null): array
    {
        $qb = $this->createQueryBuilder('t');

        foreach ($criteria as $key => $value) {
            $qb
                ->andWhere('t.'.$key.' = :'.$key)
                ->setParameter($key, $value, 'id' === $key && \is_string($value) ? Types::GUID : null)
            ;
        }

        return $this->getResult($qb, $locale);
    }

    /**
     * @param mixed $query
     *
     * @throws
     */
    public function getOneOrNullResult($query, ?string $locale = null, ?int $hydrationMode = null)
    {
        return QueryUtil::translateQuery($query, $locale)->getOneOrNullResult($hydrationMode);
    }

    /**
     * @param mixed $query
     */
    public function getResult($query, ?string $locale = null, int $hydrationMode = AbstractQuery::HYDRATE_OBJECT): array
    {
        return QueryUtil::translateQuery($query, $locale)->getResult($hydrationMode);
    }

    /**
     * @param mixed $query
     */
    public function getArrayResult($query, ?string $locale = null): array
    {
        return QueryUtil::translateQuery($query, $locale)->getArrayResult();
    }

    /**
     * @param mixed $query
     *
     * @throws
     */
    public function getSingleResult($query, ?string $locale = null, ?int $hydrationMode = null)
    {
        return QueryUtil::translateQuery($query, $locale)->getSingleResult($hydrationMode);
    }

    /**
     * @param mixed $query
     */
    public function getScalarResult($query, ?string $locale = null): array
    {
        return QueryUtil::translateQuery($query, $locale)->getScalarResult();
    }

    /**
     * @param mixed $query
     *
     * @throws
     */
    public function getSingleScalarResult($query, ?string $locale = null)
    {
        return QueryUtil::translateQuery($query, $locale)->getSingleScalarResult();
    }
}
