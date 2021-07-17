<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Pagination;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestPaginationQuery
{
    public const HINT_PAGE_NUMBER = 'klipper_doctrine_extensions_extra.pagination.page_number';

    protected RequestStack $requestStack;

    protected int $defaultSize;

    protected int $maxSize;

    /**
     * @param RequestStack $requestStack The request stack
     * @param int          $defaultSize  The default size of result
     * @param int          $maxSize      The max size of result
     */
    public function __construct(
        RequestStack $requestStack,
        int $defaultSize,
        int $maxSize
    ) {
        $this->requestStack = $requestStack;
        $this->defaultSize = $defaultSize;
        $this->maxSize = $maxSize;
    }

    /**
     * Paginate the query with request parameters.
     *
     * @param Query $query    The doctrine query
     * @param bool  $lockPage Check if the request is locked on first page
     */
    public function paginate(Query $query, bool $lockPage = false): Query
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $page = !$lockPage
                ? max(1, $request->query->getInt('page', 1))
                : 1;
            $limit = max(1, $request->query->getInt('limit', $this->defaultSize));
            $maxResult = max(1, min($limit, $this->maxSize));

            if (null !== $query->getMaxResults() && $maxResult === $this->defaultSize) {
                $maxResult = $query->getMaxResults();
            }

            $firstResult = (($page - 1) * $maxResult + 1) - 1;

            $query->setFirstResult($firstResult)->setMaxResults($maxResult);
            $query->setHint(static::HINT_PAGE_NUMBER, $page);
        }

        return $query;
    }
}
