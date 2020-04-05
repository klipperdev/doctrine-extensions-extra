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
 * Representation of the pagination.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Pagination implements PaginationInterface
{
    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $pages;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var object[]
     */
    protected $results;

    /**
     * Constructor.
     *
     * @param object[] $results The results
     * @param null|int $page    The page number
     * @param null|int $limit   The limit of pagination
     * @param null|int $pages   The number of pages
     * @param null|int $total   The size of the collection
     */
    public function __construct(
        array $results,
        ?int $page = null,
        ?int $limit = null,
        ?int $pages = null,
        ?int $total = null
    ) {
        $this->page = \is_int($page) ? $page : 1;
        $this->limit = \is_int($limit) ? $limit : max(1, \count($results));
        $this->pages = \is_int($pages) ? $pages : 1;
        $this->total = \is_int($total) ? $total : \count($results);
        $this->results = $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getPages(): int
    {
        return $this->pages;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
