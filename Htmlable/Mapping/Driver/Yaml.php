<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Htmlable\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Yaml as BaseYaml;

/**
 * The yaml mapping driver for htmlable behavioral extension.
 * Used for extraction of extended metadata from yaml
 * specifically for Htmlable extension.
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
    private $validTypes = [
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
                if (isset($fieldMapping['klipper']['htmlable'])) {
                    $mappingProperty = $fieldMapping['klipper']['htmlable'];
                    $tags = [];
                    $charset = 'UTF-8';

                    if (!$this->isValidField($meta, $field)) {
                        throw new InvalidMappingException("Field - [{$field}] type is not valid and must be 'string' or 'text' in class - {$meta->name}");
                    }

                    if (\is_array($mappingProperty['tags'])) {
                        $tags = $mappingProperty['tags'];
                    }

                    if (\is_string($mappingProperty['charset'])) {
                        $charset = $mappingProperty['charset'];
                    }

                    $config['htmlable'][$field] = [
                        'field' => $field,
                        'tags' => $tags,
                        'charset' => $charset,
                    ];
                }
            }
        }
    }

    /**
     * Checks if field type is valid.
     *
     * @param object $meta  The class metadata
     * @param string $field The field name
     */
    protected function isValidField(object $meta, string $field): bool
    {
        $mapping = $meta->getFieldMapping($field);

        return $mapping && \in_array($mapping['type'], $this->validTypes, true);
    }
}
