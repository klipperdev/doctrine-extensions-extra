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

use Doctrine\ORM\EntityManager;

/**
 * Insensitive trait for Doctrine\ORM\EntityRepository.
 *
 * @method EntityManager getEntityManager
 * @method string        getEntityName
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait InsensitiveTrait
{
    /**
     * Finds entities by a set of criteria with insensitive field.
     *
     * @return array The objects
     */
    public function findByInsensitive(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $qb->select('o')->from($this->getEntityName(), 'o');

        foreach ($criteria as $field => $value) {
            if (\in_array($field, $this->getInsensitiveFields(), true)) {
                $qb->andWhere('LOWER(o.'.$field.') = :'.$field);
                $qb->setParameter($field, \is_string($value) ? mb_strtolower($value) : $value);

                continue;
            }

            if (null === $value) {
                $qb->andWhere($expr->isNull('o.'.$field));
            } else {
                $qb->andWhere('o.'.$field.' = :'.$field);
                $qb->setParameter($field, $value);
            }
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $qb->addOrderBy('o.'.$field, $order);
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the insensitive field names.
     *
     * @return string[]
     */
    abstract protected function getInsensitiveFields(): array;
}
