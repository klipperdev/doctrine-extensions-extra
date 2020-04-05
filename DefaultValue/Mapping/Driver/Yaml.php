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

use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Yaml as BaseYaml;

/**
 * The yaml mapping driver for default value behavioral extension.
 * Used for extraction of extended metadata from yaml
 * specifically for DefaultValue extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Yaml extends BaseYaml
{
    /**
     * {@inheritdoc}
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        $mapping = $this->_getMapping($meta->name);

        if (isset($mapping['fields'])) {
            foreach ($mapping['fields'] as $field => $fieldMapping) {
                if (isset($fieldMapping['klipper']['defaultValue'])) {
                    $mappingProperty = $fieldMapping['klipper']['defaultValue'];

                    $config['defaultValue'][$field] = [
                        'field' => $field,
                        'expression' => isset($mappingProperty['expression']) ? (string) $mappingProperty['expression'] : null,
                    ];
                }
            }
        }
    }
}
