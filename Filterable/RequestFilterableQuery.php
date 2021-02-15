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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestFilterableQuery
{
    private RequestStack $requestStack;

    private FilterableQuery $filterableQuery;

    /**
     * @param RequestStack    $requestStack    The request stack
     * @param FilterableQuery $filterableQuery The filterable query
     */
    public function __construct(
        RequestStack $requestStack,
        FilterableQuery $filterableQuery
    ) {
        $this->requestStack = $requestStack;
        $this->filterableQuery = $filterableQuery;
    }

    /**
     * Filter the query.
     *
     * @param Query $query The query
     */
    public function filter(Query $query): void
    {
        $queryFilter = $this->getQueryFilter();

        if (empty($queryFilter)) {
            return;
        }

        try {
            $filter = json_decode($queryFilter, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException('The X-Filter HTTP header is not a valid JSON');
        }

        $this->filterableQuery->filter($query, $filter, FilterableQuery::VALIDATE_ALL);
    }

    /**
     * Get the request query filter.
     */
    private function getQueryFilter(): string
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-filter')) {
                return (string) $request->headers->get('x-filter', '');
            }

            return (string) $request->query->get('filter', '');
        }

        return '';
    }
}
