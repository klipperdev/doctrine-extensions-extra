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
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Xml as BaseXml;

/**
 * The xml mapping driver for default value behavioral extension.
 * Used for extraction of extended metadata from xml
 * specifically for DefaultValue extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Xml extends BaseXml
{
    /**
     * @param ClassMetadata $meta
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        /** @var \SimpleXmlElement $mapping */
        $mapping = $this->_getMapping($meta->getName());

        $this->readFieldDefaultValueMapping($meta, $config, $mapping);

        if (isset($mapping->field)) {
            /** @var \SimpleXmlElement $fieldMapping */
            foreach ($mapping->field as $fieldMapping) {
                $this->readFieldDefaultValueMapping($meta, $config, $fieldMapping);
            }
        }
    }

    /**
     * Read the field default value configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readFieldDefaultValueMapping(ClassMetadata $meta, array &$config, \SimpleXMLElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'default-value'})) {
            $this->validateElementType($meta, $mapping, 'default value', ['field', 'mapped-superclass', 'entity']);

            foreach ($ciMappings->{'default-value'} as $data) {
                $field = $this->getFieldName($meta, $mapping, $data, false, 'default-value');

                $this->mergeExtensionConfig('defaultValue', $field, $config, [
                    'field' => $field,
                    'expression' => $this->getStringAttribute($data, 'expression'),
                ]);
            }
        }
    }
}
