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

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Type\FilterableArrayType;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Type\FilterableObjectType;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\AndNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\BeginsWithNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\BetweenNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ContainsNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\EndsWithNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\EqualNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\GreaterNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\GreaterOrEqualNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\InNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\IsEmptyNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\IsFalseNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\IsNullNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\IsTrueNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\LessNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\LessOrEqualNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotBeginsWithNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotBetweenNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotContainsNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotEndsWithNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotEqualNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotInNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotIsEmptyNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NotIsNullNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\OrNode;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

/**
 * Default config of Parser.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class ParserConfig
{
    /**
     * Get the default config of node conditions.
     */
    public static function getDefaultNodeConditions(): array
    {
        return [
            'AND' => AndNode::class,
            'OR' => OrNode::class,
        ];
    }

    /**
     * Get the default config of node rules.
     */
    public static function getDefaultNodeRules(): array
    {
        return [
            'equal' => EqualNode::class,
            'not_equal' => NotEqualNode::class,
            'less' => LessNode::class,
            'less_or_equal' => LessOrEqualNode::class,
            'greater' => GreaterNode::class,
            'greater_or_equal' => GreaterOrEqualNode::class,
            'contains' => ContainsNode::class,
            'not_contains' => NotContainsNode::class,
            'begins_with' => BeginsWithNode::class,
            'not_begins_with' => NotBeginsWithNode::class,
            'ends_with' => EndsWithNode::class,
            'not_ends_with' => NotEndsWithNode::class,
            'between' => BetweenNode::class,
            'not_between' => NotBetweenNode::class,
            'in' => InNode::class,
            'not_in' => NotInNode::class,
            'is_empty' => IsEmptyNode::class,
            'is_not_empty' => NotIsEmptyNode::class,
            'is_null' => IsNullNode::class,
            'is_not_null' => NotIsNullNode::class,
            'is_true' => IsTrueNode::class,
            'is_false' => IsFalseNode::class,
        ];
    }

    /**
     * Get the default config of map rules.
     */
    public static function getDefaultMapRules(): array
    {
        return [
            'guid' => [
                'equal',
                'not_equal',
                'in',
                'not_in',
                'is_null',
            ],
            'uuid' => [
                'equal',
                'not_equal',
                'in',
                'not_in',
                'is_null',
            ],
            'string' => [
                'equal',
                'not_equal',
                'contains',
                'not_contains',
                'begins_with',
                'not_begins_with',
                'ends_with',
                'not_ends_with',
                'in',
                'not_in',
                'is_empty',
                'is_not_empty',
                'is_null',
                'is_not_null',
            ],
            'integer' => [
                'equal',
                'not_equal',
                'less',
                'less_or_equal',
                'greater',
                'greater_or_equal',
                'between',
                'not_between',
                'in',
                'not_in',
                'is_null',
                'is_not_null',
            ],
            'float' => [
                'equal',
                'not_equal',
                'less',
                'less_or_equal',
                'greater',
                'greater_or_equal',
                'between',
                'not_between',
                'in',
                'not_in',
                'is_null',
                'is_not_null',
            ],
            'boolean' => [
                'equal',
                'not_equal',
                'is_null',
                'is_not_null',
                'is_true',
                'is_false',
            ],
            'datetime' => [
                'equal',
                'not_equal',
                'less',
                'less_or_equal',
                'greater',
                'greater_or_equal',
                'between',
                'not_between',
                'is_null',
                'is_not_null',
            ],
            'date' => [
                'equal',
                'not_equal',
                'less',
                'less_or_equal',
                'greater',
                'greater_or_equal',
                'between',
                'not_between',
                'is_null',
                'is_not_null',
            ],
            'time' => [
                'equal',
                'not_equal',
                'less',
                'less_or_equal',
                'greater',
                'greater_or_equal',
                'between',
                'not_between',
                'is_null',
                'is_not_null',
            ],
            'object' => [
                'equal',
                'not_equal',
                'contains',
                'not_contains',
                'begins_with',
                'not_begins_with',
                'ends_with',
                'not_ends_with',
                'in',
                'not_in',
                'is_empty',
                'is_not_empty',
                'is_null',
                'is_not_null',
            ],
            'array' => [
                'contains',
                'not_contains',
                'is_empty',
                'is_not_empty',
            ],
        ];
    }

    /**
     * Get the default config of map forms.
     */
    public static function getDefaultMapForms(): array
    {
        return [
            'guid' => TextType::class,
            'string' => TextType::class,
            'integer' => IntegerType::class,
            'float' => NumberType::class,
            'boolean' => CheckboxType::class,
            'datetime' => [DateTimeType::class, [
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ]],
            'date' => [DateType::class, [
                'widget' => 'single_text',
                'format' => DateType::HTML5_FORMAT,
            ]],
            'time' => [TimeType::class, [
                'widget' => 'single_text',
            ]],
            'object' => FilterableObjectType::class,
            'array' => FilterableArrayType::class,
        ];
    }
}
