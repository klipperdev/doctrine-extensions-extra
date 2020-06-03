<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Form\ChoiceList;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Form\Doctrine\ChoiceList\QueryBuilderTransformer;

/**
 * Translatable query builder transformer.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableQueryBuilderTransformer extends QueryBuilderTransformer
{
    public function getQuery(QueryBuilder $qb): Query
    {
        return QueryUtil::translateQuery($qb);
    }
}
