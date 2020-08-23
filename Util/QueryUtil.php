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
     * @param MetadataManagerInterface           $metadataManager The metadata manager
     * @param ObjectMetadataInterface            $metadata        The object metadata
     * @param string[]                           $associations    The recursive association names
     * @param array                              $joins           The joins by reference
     * @param null|AuthorizationCheckerInterface $authChecker     The authorization checker
     */
    public static function getAssociationMeta(
        MetadataManagerInterface $metadataManager,
        ObjectMetadataInterface $metadata,
        array $associations,
        array &$joins,
        ?AuthorizationCheckerInterface $authChecker = null
    ): ?ObjectMetadataInterface {
        $finalMeta = $metadata;

        foreach ($associations as $association) {
            if ($finalMeta->hasAssociationByName($association)) {
                $assoMeta = $finalMeta->getAssociationByName($association);

                if (static::isAssociationVisible($finalMeta, $assoMeta, $authChecker)
                        && \in_array($assoMeta->getType(), ['one-to-one', 'many-to-one'], true)) {
                    $originClass = $finalMeta->getClass();
                    $finalMeta = $metadataManager->get($assoMeta->getTarget());
                    $joins[static::getAlias($finalMeta)] = [
                        'targetClass' => $finalMeta->getClass(),
                        'parentClass' => $originClass,
                        'relation' => $assoMeta->getAssociation(),
                    ];
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
