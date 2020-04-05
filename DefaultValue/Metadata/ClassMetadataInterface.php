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
 * The class metadata of default value.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface ClassMetadataInterface
{
    /**
     * Get the class name.
     */
    public function getClass(): string;

    /**
     * Get the field metadatas.
     *
     * @return FieldMetadataInterface[]
     */
    public function getFields(): array;

    /**
     * Check if the field is defined.
     *
     * @param string $field The field name
     */
    public function hasField(string $field): bool;

    /**
     * Get the field metadata.
     *
     * @param string $field The field name
     */
    public function getField(string $field): ?FieldMetadataInterface;

    /**
     * Merge the class metadata with another class metadata.
     *
     * @param ClassMetadataInterface $meta The class metadata
     */
    public function merge(ClassMetadataInterface $meta): void;
}
