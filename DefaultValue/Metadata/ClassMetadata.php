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
class ClassMetadata implements ClassMetadataInterface
{
    protected string $class;

    /**
     * @var FieldMetadataInterface[]
     */
    protected array $fields = [];

    public function __construct(string $class, array $fields)
    {
        $this->class = $class;

        foreach ($fields as $field) {
            if ($field instanceof FieldMetadataInterface) {
                $this->fields[$field->getField()] = $field;
            }
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function hasField(string $field): bool
    {
        return isset($this->fields[$field]);
    }

    public function getField(string $field): ?FieldMetadataInterface
    {
        return $this->fields[$field] ?? null;
    }

    public function merge(ClassMetadataInterface $meta): void
    {
        if ($this->class !== $meta->getClass()) {
            return;
        }

        foreach ($meta->getFields() as $fieldMeta) {
            if ($this->hasField($fieldMeta->getField())) {
                $this->fields[$fieldMeta->getField()]->merge($fieldMeta);
            } else {
                $this->fields[$fieldMeta->getField()] = clone $fieldMeta;
            }
        }
    }
}
