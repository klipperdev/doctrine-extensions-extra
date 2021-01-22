<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser;

use Doctrine\ORM\Query\Parameter;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidAssociationException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers\NodeFieldNameTransformerInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers\NodeValueTransformerInterface;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\FieldMetadataInterface;

/**
 * Utils for parser.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class ParserUtil
{
    /**
     * Get the field metadata.
     *
     * @param CompileArgs $args The compiler arguments
     * @param RuleNode    $node The rule node
     */
    public static function getFieldMetadata(CompileArgs $args, RuleNode $node): FieldMetadataInterface
    {
        $metadataManager = $args->getMetadataManager();
        $metadata = $args->getObjectMetadata();
        $field = $node->getField();

        if (false !== strpos($field, '.')) {
            $associations = explode('.', $field);
            $field = array_pop($associations);

            foreach ($associations as $association) {
                $assoMeta = $metadata->getAssociationByName($association);
                $metadata = $metadataManager->get($assoMeta->getTarget());
            }
        }

        return $metadata->getFieldByName($field);
    }

    /**
     * Get the field name with alias.
     *
     * @param CompileArgs $args The compiler arguments
     * @param RuleNode    $node The rule node
     */
    public static function getFieldName(CompileArgs $args, RuleNode $node): string
    {
        $nodeValue = $node->getQueryValue();
        $field = $node->getField();
        $alias = $args->getAlias();

        if (false !== strpos($field, '.')) {
            $links = explode('.', $field);
            $field = array_pop($links);

            $metadataManager = $args->getMetadataManager();
            $joinAliases = $args->getJoinAliases();
            $linkMeta = $args->getObjectMetadata();

            foreach ($links as $link) {
                if (!$linkMeta->hasAssociationByName($link)) {
                    throw new InvalidAssociationException($node->getField());
                }

                $assoMeta = $linkMeta->getAssociationByName($link);

                if (!\in_array($assoMeta->getType(), ['one-to-one', 'many-to-one'], true)) {
                    throw new InvalidAssociationException($node->getField());
                }

                $linkMeta = $metadataManager->get($assoMeta->getTarget());
                $linkAssociation = $alias.'.'.$assoMeta->getAssociation();

                $alias = $joinAliases[$linkAssociation]
                    ?? QueryUtil::getAlias($linkMeta);
            }
        }

        $fieldName = $alias.'.'.$field;

        if ($nodeValue instanceof NodeFieldNameTransformerInterface) {
            return $nodeValue->compileFieldName($args, $node, $fieldName);
        }

        return $fieldName;
    }

    /**
     * Set the rule node value in query parameter and returns the key parameter.
     *
     * @param CompileArgs $args The compiler arguments
     * @param RuleNode    $node The rule node
     */
    public static function setNodeValue(CompileArgs $args, RuleNode $node): string
    {
        return static::setValue($args, $node->getField(), $node->getQueryValue());
    }

    /**
     * Set the field value in query parameter and returns the key parameter.
     *
     * @param CompileArgs $args  The compiler arguments
     * @param string      $field The field name
     * @param mixed       $value The value
     */
    public static function setValue(CompileArgs $args, string $field, $value): string
    {
        if ($value instanceof NodeValueTransformerInterface) {
            return $value->compileValue($args, $field);
        }

        $pos = $args->getParameters()->count() + 1;
        $key = 'filter_'.str_replace(['.'], '_', $field).'_'.$pos;
        $args->getParameters()->add(new Parameter($key, $value));

        return ':'.$key;
    }
}
