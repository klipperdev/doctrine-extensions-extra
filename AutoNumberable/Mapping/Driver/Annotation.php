<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\AutoNumberable;

/**
 * The annotation mapping driver for auto numberable behavioral extension.
 * Used for extraction of extended metadata from Annotations
 * specifically for AutoNumberable extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation field is auto numberable.
     */
    public const AUTO_NUMERABLE = AutoNumberable::class;

    /**
     * List of types which are valid for html.
     *
     * @var string[]
     */
    protected $validTypes = [
        'string',
        'text',
    ];

    /**
     * {@inheritdoc}
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

            /** @var AutoNumberable $autoNumberable */
            if ($autoNumberable = $this->reader->getPropertyAnnotation($property, self::AUTO_NUMERABLE)) {
                $field = $property->getName();

                if (!$meta->hasField($field)) {
                    throw new InvalidMappingException("Unable to find auto numberable [{$field}] as mapped property in entity - {$meta->name}");
                }

                if (!$this->isValidField($meta, $field)) {
                    throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'string' or 'text' in class - {$meta->name}");
                }

                $config['autoNumberable'][$field] = [
                    'field' => $field,
                    'pattern' => $autoNumberable->pattern,
                    'utc' => $autoNumberable->utc,
                    'condition' => $autoNumberable->condition,
                ];
            }
        }
    }
}
