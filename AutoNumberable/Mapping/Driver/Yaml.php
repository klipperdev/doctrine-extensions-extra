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

use Doctrine\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Yaml as BaseYaml;

/**
 * The yaml mapping driver for auto numberable behavioral extension.
 * Used for extraction of extended metadata from yaml
 * specifically for AutoNumberable extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Yaml extends BaseYaml
{
    /**
     * List of types which are valid for html.
     *
     * @var string[]
     */
    private array $validTypes = [
        'string',
        'text',
    ];

    /**
     * @param ClassMetadata $meta
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        $mapping = $this->_getMapping($meta->name);

        if (isset($mapping['fields'])) {
            foreach ($mapping['fields'] as $field => $fieldMapping) {
                if (isset($fieldMapping['klipper']['autoNumberable'])) {
                    $mappingProperty = $fieldMapping['klipper']['autoNumberable'];

                    if (!$this->isValidField($meta, $field)) {
                        throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'string' or 'text' in class - {$meta->name}");
                    }

                    $config['autoNumberable'][$field] = [
                        'field' => $field,
                        'pattern' => isset($mappingProperty['pattern']) ? (string) $mappingProperty['pattern'] : null,
                        'utc' => isset($mappingProperty['utc']) ? (bool) $mappingProperty['utc'] : false,
                        'condition' => isset($mappingProperty['condition']) ? (string) $mappingProperty['condition'] : null,
                    ];
                }
            }
        }
    }

    /**
     * Checks if field type is valid.
     *
     * @param ClassMetadata $meta  The class metadata
     * @param string        $field The field name
     */
    protected function isValidField(ClassMetadata $meta, string $field): bool
    {
        $mapping = $meta->getFieldMapping($field);

        return $mapping && \in_array($mapping['type'], $this->validTypes, true);
    }
}
