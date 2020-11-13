<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensions\Filter\AbstractFilter;
use Klipper\Component\Object\Util\ClassUtil;
use Klipper\Contracts\Model\ExpirableInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ExpirableFilter extends AbstractFilter
{
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return ClassUtil::isInstanceOf($targetEntity->reflClass, ExpirableInterface::class);
    }

    /**
     * @throws
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $conn = $this->getEntityManager()->getConnection();
        $platform = $conn->getDatabasePlatform();
        $column = $targetEntity->getColumnName('expiresAt');

        $addCondSql = $platform->getIsNullExpression($targetTableAlias.'.'.$column);

        $currentTimestamp = $platform->getCurrentTimestampSQL();
        $this->setParameter('currentTimestamp', $currentTimestamp);

        return "({$addCondSql} OR {$targetTableAlias}.{$column} > {$currentTimestamp})";
    }
}
