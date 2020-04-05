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
interface FieldMetadataInterface
{
    /**
     * Get the field name.
     */
    public function getField(): string;

    /**
     * Get the expression.
     */
    public function getExpression(): ?string;

    /**
     * Merge the field metadata with another field metadata.
     *
     * @param FieldMetadataInterface $meta The field metadata
     */
    public function merge(FieldMetadataInterface $meta): void;
}
