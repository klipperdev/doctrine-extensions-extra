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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\Form\Doctrine\ChoiceList\ORMQueryBuilderLoader;

/**
 * Filterable ORM Query Builder Loader.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableORMQueryBuilderLoader extends ORMQueryBuilderLoader
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string[]
     */
    private $filters;

    /**
     * @var string[]
     */
    private $usingFilters = [];

    /**
     * Constructor.
     *
     * @param QueryBuilder $queryBuilder The query builder for creating the query builder
     * @param string[]     $filters      The sql filters
     */
    public function __construct(QueryBuilder $queryBuilder, array $filters = [])
    {
        parent::__construct($queryBuilder);

        $this->em = $queryBuilder->getEntityManager();
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    protected function preLoad(): void
    {
        $this->usingFilters = SqlFilterUtil::findFilters($this->em, $this->filters);
        SqlFilterUtil::disableFilters($this->em, $this->usingFilters);
    }

    /**
     * {@inheritdoc}
     */
    protected function postLoad(): void
    {
        SqlFilterUtil::enableFilters($this->em, $this->usingFilters);
        $this->usingFilters = [];
    }
}
