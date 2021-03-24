<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Klipper\Component\Metadata\AssociationMetadataInterface;
use Klipper\Component\Metadata\FieldMetadataInterface;
use Klipper\Component\Metadata\MetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Klipper\Component\Security\Permission\FieldVote;
use Klipper\Component\Security\Permission\PermVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class QueryUtil
{
    private static ?bool $translatable = null;

    /**
     * Inject the translation walker in the Doctrine query instance.
     *
     * @param Query|QueryBuilder $query  A Doctrine query or query builder instance
     * @param string             $locale A locale name
     */
    public static function translateQuery($query, ?string $locale = null): Query
    {
        $query = $query instanceof QueryBuilder ? $query->getQuery() : $query;
        $locale = $locale ?? \Locale::getDefault();

        if (self::isTranslatable($query) && false === $query->getHint(Query::HINT_CUSTOM_OUTPUT_WALKER)) {
            $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
            $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);
        }

        return $query;
    }

    /**
     * Attach the custom tree walker.
     *
     * @param Query  $query      The query
     * @param string $treeWalker The tree walker class
     */
    public static function addCustomTreeWalker(Query $query, string $treeWalker): void
    {
        $customTreeWalkers = $query->getHint(Query::HINT_CUSTOM_TREE_WALKERS);

        if (!\is_array($customTreeWalkers)) {
            $customTreeWalkers = [];
        }

        if (\in_array($treeWalker, $customTreeWalkers, true)) {
            return;
        }

        $customTreeWalkers[] = $treeWalker;
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $customTreeWalkers);
    }

    /**
     * Inject joins of the original query into the new query builder.
     */
    public static function injectOriginalJoins(QueryBuilder $qb, Query $originalQuery): void
    {
        // Add same joins like original query
        $originalAST = $originalQuery->getAST();
        /** @var Query\AST\IdentificationVariableDeclaration $originalIdVarDeclaration */
        $originalIdVarDeclaration = $originalAST->fromClause->identificationVariableDeclarations[0];

        if ($originalIdVarDeclaration instanceof Query\AST\IdentificationVariableDeclaration) {
            /** @var Query\AST\Join $join */
            foreach ($originalIdVarDeclaration->joins as $join) {
                /** @var Query\AST\JoinAssociationDeclaration $declaration */
                $declaration = $join->joinAssociationDeclaration;
                $joinPath = $declaration->joinAssociationPathExpression;
                $qbJoinAssociation = $joinPath->identificationVariable.'.'.$joinPath->associationField;

                if (Query\AST\Join::JOIN_TYPE_LEFT === $join->joinType) {
                    $qb->leftJoin($qbJoinAssociation, $declaration->aliasIdentificationVariable);
                } else {
                    $qb->join($qbJoinAssociation, $declaration->aliasIdentificationVariable);
                }
            }
        }
    }

    /**
     * Get the map of join associations and entity aliases.
     *
     * @param Query|QueryBuilder $query
     *
     * @return string[]
     */
    public static function getJoinAliases($query): array
    {
        $joinAliases = [];

        if ($query instanceof QueryBuilder) {
            $parts = $query->getDQLParts();

            if (isset($parts['join']) && \is_array($parts['join']) && \count($parts['join']) > 0) {
                /** @var Query\Expr\Join[] $joins */
                foreach ($parts['join'] as $alias => $joins) {
                    foreach ($joins as $join) {
                        $joinAliases[$join->getJoin()] = $join->getAlias();
                    }
                }
            }
        } elseif ($query instanceof Query) {
            $AST = $query->getAST();
            /** @var Query\AST\IdentificationVariableDeclaration $idVarDeclaration */
            $idVarDeclaration = $AST->fromClause->identificationVariableDeclarations[0];

            if ($idVarDeclaration instanceof Query\AST\IdentificationVariableDeclaration) {
                /** @var Query\AST\Join $join */
                foreach ($idVarDeclaration->joins as $join) {
                    /** @var Query\AST\JoinAssociationDeclaration $declaration */
                    $declaration = $join->joinAssociationDeclaration;
                    $joinPath = $declaration->joinAssociationPathExpression;
                    $qbJoinAssociation = $joinPath->identificationVariable.'.'.$joinPath->associationField;
                    $joinAliases[$qbJoinAssociation] = $declaration->aliasIdentificationVariable;
                }
            }
        }

        return $joinAliases;
    }

    /**
     * Get the doctrine compatible alias name from metadata name.
     *
     * @param MetadataInterface|string $object The object name or metadata
     */
    public static function getAlias($object): string
    {
        if ($object instanceof MetadataInterface) {
            $object = $object->getName();
        }

        return '_'.$object;
    }

    /**
     * Check if the field is visible.
     *
     * @param ObjectMetadataInterface            $metadata    The object metadata
     * @param FieldMetadataInterface             $fieldMeta   The field metadata
     * @param null|AuthorizationCheckerInterface $authChecker The authorization checker
     */
    public static function isFieldVisible(
        ObjectMetadataInterface $metadata,
        FieldMetadataInterface $fieldMeta,
        ?AuthorizationCheckerInterface $authChecker = null
    ): bool {
        return $fieldMeta->isPublic()
            && (!$authChecker || $authChecker->isGranted(new PermVote('read'), new FieldVote($metadata->getClass(), $fieldMeta->getField())));
    }

    /**
     * Check if the association is visible.
     *
     * @param ObjectMetadataInterface            $metadata    The object metadata
     * @param AssociationMetadataInterface       $assoMeta    The association metadata
     * @param null|AuthorizationCheckerInterface $authChecker The authorization checker
     */
    public static function isAssociationVisible(
        ObjectMetadataInterface $metadata,
        AssociationMetadataInterface $assoMeta,
        ?AuthorizationCheckerInterface $authChecker = null
    ): bool {
        return $assoMeta->isPublic()
            && (!$authChecker || $authChecker->isGranted(new PermVote('read'), new FieldVote($metadata->getClass(), $assoMeta->getAssociation())));
    }

    /**
     * Get the object metadata of the target association.
     *
     * @param MetadataManagerInterface           $metadataManager    The metadata manager
     * @param ObjectMetadataInterface            $metadata           The object metadata
     * @param string[]                           $associations       The recursive association names
     * @param array                              $joins              The joins by reference
     * @param null|AuthorizationCheckerInterface $authChecker        The authorization checker
     * @param null|string                        $alias              The alias of object metadata
     * @param null|Query                         $query              The query to check if join already exists
     * @param null|string                        $existingFinalAlias The existing final alias retrieve with the query
     */
    public static function getAssociationMeta(
        MetadataManagerInterface $metadataManager,
        ObjectMetadataInterface $metadata,
        array $associations,
        ?array &$joins = null,
        ?AuthorizationCheckerInterface $authChecker = null,
        ?string $alias = null,
        ?Query $query = null,
        ?string &$existingFinalAlias = null
    ): ?ObjectMetadataInterface {
        $finalMeta = $metadata;
        $finalAlias = $alias;

        foreach ($associations as $i => $association) {
            if ($finalMeta->hasAssociationByName($association)) {
                $assoMeta = $finalMeta->getAssociationByName($association);

                if (static::isAssociationVisible($finalMeta, $assoMeta, $authChecker)
                        && \in_array($assoMeta->getType(), ['one-to-one', 'many-to-one'], true)) {
                    $originClass = $finalMeta->getClass();
                    $finalMeta = $metadataManager->get($assoMeta->getTarget());
                    $existingFinalAlias = static::getExistingAlias($assoMeta, $finalAlias, $query);

                    if (null === $alias || null === $existingFinalAlias) {
                        $joins[static::getAlias($finalMeta)] = [
                            'targetClass' => $finalMeta->getClass(),
                            'parentClass' => $originClass,
                            'relation' => $assoMeta->getAssociation(),
                            'joinAssociation' => null !== $alias ? $finalAlias.'.'.$assoMeta->getAssociation() : $finalMeta->getClass(),
                            'identificationVariable' => $finalAlias,
                            'nestingLevel' => $i,
                        ];
                    }

                    $finalAlias = null !== $existingFinalAlias ? $existingFinalAlias : static::getAlias($finalMeta);
                } else {
                    $finalMeta = null;

                    break;
                }
            } else {
                $finalMeta = null;

                break;
            }
        }

        return $finalMeta;
    }

    private static function getExistingAlias(AssociationMetadataInterface $assoMeta, ?string $finalAlias, ?Query $query = null): ?string
    {
        if (null !== $query) {
            $AST = $query->getAST();
            /** @var Query\AST\IdentificationVariableDeclaration $idVarDeclaration */
            $idVarDeclaration = $AST->fromClause->identificationVariableDeclarations[0];
            $association = $assoMeta->getAssociation();

            /** @var Query\AST\Join $join */
            foreach ($idVarDeclaration->joins as $join) {
                /** @var Query\AST\JoinAssociationDeclaration $declaration */
                $declaration = $join->joinAssociationDeclaration;
                $joinPath = $declaration->joinAssociationPathExpression;

                if ($finalAlias === $joinPath->identificationVariable && $association === $joinPath->associationField) {
                    return $declaration->aliasIdentificationVariable;
                }
            }
        }

        return null;
    }

    /**
     * Check if the translatable is enabled.
     *
     * @param Query $query The Doctrine query
     */
    private static function isTranslatable(Query $query): bool
    {
        if (null === self::$translatable) {
            self::$translatable = false;
            $evm = $query->getEntityManager()->getEventManager();
            $tkc = TranslationWalker::class;

            if (class_exists($tkc) && $evm->hasListeners('postLoad')) {
                foreach ($evm->getListeners('postLoad') as $listener) {
                    if (TranslatableListener::class === \get_class($listener)) {
                        self::$translatable = true;

                        break;
                    }
                }
            }
        }

        return self::$translatable;
    }
}
