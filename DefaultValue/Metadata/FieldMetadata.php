<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Metadata;

/**
 * The field metadata of default value.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FieldMetadata implements FieldMetadataInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var null|string
     */
    protected $expression;

    /**
     * Constructor.
     *
     * @param string      $field      The field name
     * @param null|string $expression The expression
     */
    public function __construct(string $field, ?string $expression)
    {
        $this->field = $field;
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(FieldMetadataInterface $meta): void
    {
        if ($this->field !== $meta->getField()) {
            return;
        }

        if (null !== $pattern = $meta->getExpression()) {
            $this->expression = $pattern;
        }
    }
}
