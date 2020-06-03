<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Mapping\Driver\AbstractAnnotationDriver;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\DefaultValue;

/**
 * The annotation mapping driver for default value behavioral extension.
 * Used for extraction of extended metadata from Annotations
 * specifically for DefaultValue extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation field is default valuable.
     */
    public const DEFAULT_VALUE = DefaultValue::class;

    /**
     * @param ClassMetadata $meta
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        $class = $this->getMetaReflectionClass($meta);

        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && (!$property->isPrivate()
                    || $meta->isInheritedField($property->name)
                    || isset($meta->associationMappings[$property->name]['inherited']))) {
                continue;
            }

            /** @var DefaultValue $defaultValue */
            if ($defaultValue = $this->reader->getPropertyAnnotation($property, self::DEFAULT_VALUE)) {
                $field = $property->getName();

                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find default value [{$field}] as mapped property in entity - {$meta->name}");
                }

                $config['defaultValue'][$field] = [
                    'field' => $field,
                    'expression' => $defaultValue->expression,
                ];
            }
        }
    }
}
