<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Searchable;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\JoinsWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\MergeConditionalExpressionWalker;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestSearchableQuery
{
    protected RequestStack $requestStack;

    protected MetadataManagerInterface $metadataManager;

    protected ?AuthorizationCheckerInterface $authChecker;

    /**
     * @param RequestStack                       $requestStack    The request stack
     * @param MetadataManagerInterface           $metadataManager The metadata manager
     * @param null|AuthorizationCheckerInterface $authChecker     The authorization checker
     */
    public function __construct(
        RequestStack $requestStack,
        MetadataManagerInterface $metadataManager,
        ?AuthorizationCheckerInterface $authChecker = null
    ) {
        $this->requestStack = $requestStack;
        $this->metadataManager = $metadataManager;
        $this->authChecker = $authChecker;
    }

    /**
     * Filter the query.
     *
     * @param Query $query The query
     */
    public function filter(Query $query): void
    {
        /** @var Query\AST\IdentificationVariableDeclaration[] $varDeclarations */
        $varDeclarations = $query->getAST()->fromClause->identificationVariableDeclarations;
        $class = null;

        foreach ($varDeclarations as $varDeclaration) {
            $rangeDeclaration = $varDeclaration->rangeVariableDeclaration;
            $class = $rangeDeclaration->abstractSchemaName;

            if ($rangeDeclaration->isRoot && $this->metadataManager->has($class)) {
                $this->doFilter($query, $class, $rangeDeclaration->aliasIdentificationVariable);

                break;
            }
        }
    }

    /**
     * Filter the query.
     *
     * @param Query  $query The query
     * @param string $class The root class name
     * @param string $alias The alias
     */
    protected function doFilter(Query $query, string $class, string $alias): void
    {
        $querySearch = $this->getQuerySearch();

        if (empty($querySearch)) {
            return;
        }

        $searchableFields = $this->getSearchableFields($query, $class, $alias);

        if (empty($searchableFields->getFields())) {
            return;
        }

        QueryUtil::addCustomTreeWalker($query, JoinsWalker::class);
        QueryUtil::addCustomTreeWalker($query, MergeConditionalExpressionWalker::class);

        $qb = $this->getQueryBuilder($query->getEntityManager(), $class, $alias);
        $queryAst = $this->injectFilter($qb, $searchableFields, $querySearch)
            ->getQuery()
            ->getAST()
        ;

        /** @var Query\Parameter $param */
        foreach ($qb->getParameters()->toArray() as $param) {
            $query->setParameter($param->getName(), $param->getValue(), $param->getType());
        }

        if (MergeConditionalExpressionWalker::hasMergeableExpression($queryAst)) {
            MergeConditionalExpressionWalker::addHint($query, $queryAst);
        }

        JoinsWalker::addHint($query, $searchableFields->getJoins());
    }

    /**
     * Get the request query search.
     */
    private function getQuerySearch(): string
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-search')) {
                return $this->utf8Encode((string) $request->headers->get('x-search', ''));
            }

            return (string) $request->query->get('search', '');
        }

        return '';
    }

    /**
     * Get the searchable field names with they alias.
     *
     * @param Query  $query       The query
     * @param string $class       The class name
     * @param string $alias       The alias
     * @param string $fieldPrefix The prefix path of search field
     */
    private function getSearchableFields(Query $query, string $class, string $alias, string $fieldPrefix = ''): SearchableFields
    {
        $em = $query->getEntityManager();
        $meta = $this->metadataManager->get($class);
        $fields = $this->findSearchableFields($em, $meta, $alias);
        $joins = [];

        foreach ($meta->getDeepSearchPaths() as $path) {
            $deepJoins = [];
            $deepMeta = QueryUtil::getAssociationMeta(
                $this->metadataManager,
                $meta,
                explode('.', $path),
                $deepJoins,
                $this->authChecker,
                $alias,
                $query
            );

            if (null !== $deepMeta) {
                $deepFieldPrefix = ($fieldPrefix ? $fieldPrefix.'.' : '').$path;
                $deepFields = $this->findSearchableFields($em, $deepMeta, QueryUtil::getAlias($deepMeta), $deepFieldPrefix);

                if (!empty($deepFields)) {
                    $fields = array_merge($fields, $deepFields);
                    $joins = array_merge($joins, $deepJoins);
                }
            }
        }

        return new SearchableFields(array_unique($fields), $joins);
    }

    /**
     * Find the searchable fields for the object metadata.
     *
     * @param EntityManagerInterface  $em          The entity manager
     * @param ObjectMetadataInterface $meta        The object metadata
     * @param string                  $alias       The alias
     * @param string                  $fieldPrefix The prefix path of search field
     *
     * @return string[]
     */
    private function findSearchableFields(EntityManagerInterface $em, ObjectMetadataInterface $meta, string $alias, string $fieldPrefix = ''): array
    {
        $fields = [];
        $classMeta = $em->getClassMetadata($meta->getClass());

        if ($meta->isSearchable()) {
            foreach ($meta->getFields() as $fieldMeta) {
                $fieldName = $fieldMeta->getField();
                $fieldPath = ($fieldPrefix ? $fieldPrefix.'.' : '').$fieldMeta->getName();

                if ($fieldMeta->isSearchable() && $classMeta->hasField($fieldName)
                    && QueryUtil::isFieldVisible($meta, $fieldMeta, $this->authChecker)
                    && $this->hasRequestFields($fieldPath)
                ) {
                    $fields[] = $alias.'.'.$fieldName;
                }
            }
        }

        return $fields;
    }

    /**
     * Get the query builder.
     *
     * @param EntityManagerInterface $em    The entity manager
     * @param string                 $class The class name
     * @param string                 $alias The alias
     */
    private function getQueryBuilder(EntityManagerInterface $em, string $class, string $alias): QueryBuilder
    {
        return (new QueryBuilder($em))
            ->select($alias)
            ->from($class, $alias)
        ;
    }

    /**
     * Inject the filter in the query builder.
     *
     * @param QueryBuilder     $qb               The query builder for filter
     * @param SearchableFields $searchableFields The searchable fields
     * @param string           $queryFilter      The request query filter
     */
    private function injectFilter(QueryBuilder $qb, SearchableFields $searchableFields, string $queryFilter): QueryBuilder
    {
        $values = array_map('trim', explode(' ', $queryFilter));
        $filter = '';

        foreach ($searchableFields->getFields() as $field) {
            $filter .= '' === $filter ? '(' : ' OR (';

            foreach ($values as $i => $value) {
                $key = 'search_'.str_replace(['.'], '_', $field).'_'.($i + 1);
                $qb->setParameter($key, '%'.$value.'%');
                $filter .= 0 === $i ? '' : ' AND ';
                $filter .= 'UNACCENT(LOWER('.$field.')) LIKE UNACCENT(LOWER(:'.$key.'))';
            }

            $filter .= ')';
        }

        foreach ($searchableFields->getJoins() as $joinAlias => $joinConfig) {
            $qb->leftJoin($joinConfig['joinAssociation'], $joinAlias);
        }

        return $qb->andWhere($filter);
    }

    private function hasRequestFields(string $field): bool
    {
        $fields = $this->getRequestFields();

        return empty($fields) || \in_array($field, $fields, true);
    }

    /**
     * Get the fields config in request.
     *
     * @return string[]
     */
    private function getRequestFields(): array
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-search-fields')) {
                $fields = (string) $request->headers->get('x-search-fields', '');
            } else {
                $fields = (string) $request->query->get('search-fields', '');
            }

            $requestFields = array_map('trim', explode(',', $fields));

            return !empty($requestFields) && '' !== $requestFields[0] ? $requestFields : [];
        }

        return [];
    }

    /**
     * Converts a non-UTF-8 string to UTF-8.
     *
     * @return null|string The string converted to UTF-8
     */
    private function utf8Encode(?string $s): ?string
    {
        if (null === $s || preg_match('//u', $s)) {
            return $s;
        }

        if (!\function_exists('iconv')) {
            throw new \RuntimeException('Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.');
        }

        $charset = ini_get('php.output_encoding') ?: ini_get('default_charset') ?: 'UTF-8';

        if (false !== $c = @iconv($charset, 'UTF-8', $s)) {
            return $c;
        }
        if ('CP1252' !== $charset && false !== $c = @iconv('CP1252', 'UTF-8', $s)) {
            return $c;
        }

        return iconv('CP850', 'UTF-8', $s);
    }
}
