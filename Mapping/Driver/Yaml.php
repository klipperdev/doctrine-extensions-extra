<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver;

use Gedmo\Mapping\Driver\File;

/**
 * The mapping YamlDriver abstract class, defines the
 * metadata extraction function common among all
 * all drivers used on these extensions by file based
 * drivers.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class Yaml extends File
{
    /**
     * @var string
     */
    protected $_extension = '.dcm.yml';

    /**
     * {@inheritdoc}
     */
    protected function _loadMappingFile($file)
    {
        return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
    }

    /**
     * Get the config mapping of extension.
     *
     * @param array  $mapping The mapping
     * @param string $key     The key of mapping extension
     */
    protected function getMappingData(array $mapping, string $key): ?array
    {
        return isset($mapping['klipper'][$key])
            ? (array) $mapping['klipper'][$key]
            : null;
    }

    /**
     * Get the boolean value of attribute.
     *
     * @param array     $data      The data element
     * @param string    $attribute The attribute name
     * @param null|bool $default   The default value
     */
    protected function getBooleanAttribute(array $data, string $attribute, ?bool $default = null): ?bool
    {
        return \array_key_exists($attribute, $data)
            ? (bool) $data[$attribute]
            : $default;
    }

    /**
     * Get the string value of attribute.
     *
     * @param array       $data      The data element
     * @param string      $attribute The attribute name
     * @param null|string $default   The default value
     */
    protected function getStringAttribute(array $data, string $attribute, ?string $default = null): ?string
    {
        return \array_key_exists($attribute, $data) && null !== $data[$attribute]
            ? (string) $data[$attribute]
            : $default;
    }

    /**
     * Get the array value of attribute.
     *
     * @param array      $data      The data element
     * @param string     $attribute The attribute name
     * @param null|array $default   The default value
     */
    protected function getArrayAttribute(array $data, string $attribute, ?array $default = null): ?array
    {
        return \array_key_exists($attribute, $data) && \is_array($data[$attribute])
            ? (array) $data[$attribute]
            : $default;
    }
}
