<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Sortable;

use Doctrine\ORM\Query;
use Klipper\Component\DoctrineExtensions\ORM\Query\OrderByWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\JoinsWalker;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Klipper\Component\Metadata\Util\MetadataUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestSortableQuery
{
    protected RequestStack $requestStack;

    protected MetadataManagerInterface $metadataManager;

    protected ?AuthorizationCheckerInterface $authChecker;

    protected array $joins = [];

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
     * Sort the query.
     *
     * @param Query $query The query
     */
    public function sort(Query $query): void
    {
        /** @var Query\AST\IdentificationVariableDeclaration[] $varDeclarations */
        $varDeclarations = $query->getAST()->fromClause->identificationVariableDeclarations;

        foreach ($varDeclarations as $varDeclaration) {
            $rangeDeclaration = $varDeclaration->rangeVariableDeclaration;
            $class = $rangeDeclaration->abstractSchemaName;

            if ($rangeDeclaration->isRoot && $this->metadataManager->has($class)) {
                $this->doSort($query, $class, $rangeDeclaration->aliasIdentificationVariable);

                break;
            }
        }
    }

    /**
     * Sort the query.
     *
     * @param Query  $query The query
     * @param string $class The root class name
     * @param string $alias The alias
     */
    protected function doSort(Query $query, string $class, string $alias): void
    {
        $meta = $this->metadataManager->get($class);
        $sortable = $this->getSortable($query, $meta, $alias);

        if (empty($sortable)) {
            return;
        }

        QueryUtil::addCustomTreeWalker($query, JoinsWalker::class);
        QueryUtil::addCustomTreeWalker($query, OrderByWalker::class);
        $aliases = [];
        $fieldNames = [];
        $sorts = [];

        foreach ($sortable as $field => $direction) {
            $fieldAlias = $alias;
            $fieldName = $field;

            if (\count($exp = explode('.', $field)) > 1) {
                $fieldAlias = $exp[0];
                $fieldName = $exp[1];
            }

            $aliases[] = $fieldAlias;
            $fieldNames[] = $fieldName;
            $sorts[] = $direction;
        }

        OrderByWalker::addHint($query, $aliases, $fieldNames, $sorts);
        JoinsWalker::addHint($query, $this->joins);
        $this->joins = [];
    }

    /**
     * Get the fields and direction to sort the query.
     *
     * @param Query                   $query    The query
     * @param ObjectMetadataInterface $metadata The object metadata
     * @param string                  $alias    The alias of object metadata
     */
    protected function getSortable(Query $query, ObjectMetadataInterface $metadata, string $alias): array
    {
        if (!$metadata->isSortable()) {
            return [];
        }

        $finalSortable = [];
        $sortable = null === $query->getAST()->orderByClause ? $metadata->getDefaultSortable() : [];

        if ($request = $this->requestStack->getCurrentRequest()) {
            $querySortable = $request->headers->has('x-sort')
                ? $request->headers->get('x-sort')
                : $request->query->get('sort');
            $sortable = !empty($querySortable)
                ? $this->getObjectSortable($metadata, $querySortable)
                : $sortable;
        }

        if (!$metadata->isMultiSortable() && \count($sortable) > 1) {
            $firstKey = array_keys($sortable)[0];
            $sortable = [$firstKey => $sortable[$firstKey]];
        }

        foreach ($sortable as $field => $direction) {
            $metaForField = $metadata;
            $joins = [];
            $existingFinalAlias = null;

            if (false !== strpos($field, '.')) {
                $links = explode('.', $field);
                $field = array_pop($links);
                $metaForField = QueryUtil::getAssociationMeta(
                    $this->metadataManager,
                    $metadata,
                    $links,
                    $joins,
                    $this->authChecker,
                    $alias,
                    $query,
                    $existingFinalAlias
                );
            }

            $fieldMeta = $metaForField && $metaForField->hasFieldByName($field)
                ? $metaForField->getFieldByName($field)
                : null;

            if ($fieldMeta && $fieldMeta->isSortable() && QueryUtil::isFieldVisible($metaForField, $fieldMeta, $this->authChecker)) {
                if (null !== $existingFinalAlias) {
                    $field = $existingFinalAlias.'.'.$fieldMeta->getField();
                } elseif ($metaForField && $metadata !== $metaForField) {
                    $field = QueryUtil::getAlias($metaForField).'.'.$fieldMeta->getField();
                } else {
                    $field = $fieldMeta->getField();
                }

                $finalSortable[$field] = $direction;
                $this->joins = array_merge($joins, $this->joins);
            }
        }

        return $finalSortable;
    }

    /**
     * Get the request sortable configuration for the metadata.
     *
     * @param ObjectMetadataInterface $metadata      The object metadata
     * @param string                  $querySortable The query sortable
     */
    protected function getObjectSortable(ObjectMetadataInterface $metadata, string $querySortable): array
    {
        $sortable = MetadataUtil::getDefaultSortable(trim(trim($querySortable, '\''), '"'));
        $name = $metadata->getName();
        $validSortable = [];

        foreach ($sortable as $field => $direction) {
            if (false !== ($pos = strrpos($field, '.'))) {
                if (0 === strpos($field, $name)) {
                    $finalField = substr($field, $pos + 1);

                    if ($metadata->hasFieldByName($finalField)) {
                        $validSortable[$finalField] = $direction;

                        continue;
                    }
                }

                $associations = explode('.', $field);
                $finalField = array_pop($associations);
                $finalAssociationMeta = $metadata;

                foreach ($associations as $association) {
                    if ($finalAssociationMeta->hasAssociationByName($association)) {
                        $assoMeta = $finalAssociationMeta->getAssociationByName($association);
                        $finalAssociationMeta = $this->metadataManager->get($assoMeta->getTarget());
                    } else {
                        $finalAssociationMeta = null;

                        break;
                    }
                }

                if (null !== $finalAssociationMeta && $finalAssociationMeta->hasFieldByName($finalField)) {
                    $validSortable[$field] = $direction;
                }
            } elseif ($metadata->hasFieldByName($field)) {
                $validSortable[$field] = $direction;
            }
        }

        return $validSortable;
    }
}
