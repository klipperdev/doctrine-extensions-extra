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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Xml as BaseXml;

/**
 * The xml mapping driver for htmlable behavioral extension.
 * Used for extraction of extended metadata from xml
 * specifically for Htmlable extension.
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

        if (isset($mapping->field)) {
            /** @var \SimpleXmlElement $fieldMapping */
            foreach ($mapping->field as $fieldMapping) {
                $this->readFieldMetadataMapping($meta, $config, $fieldMapping);
            }
        }
    }

    /**
     * Read the field htmlable configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readFieldMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXmlElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'htmlable'})) {
            $this->validateElementType($meta, $mapping, 'htmlable', ['field', 'mapped-superclass', 'entity']);

            foreach ($ciMappings->{'htmlable'} as $data) {
                $field = $this->getFieldName($meta, $mapping, $data, false, 'htmlable');
                $this->validateField($meta, $field, ['string', 'text']);

                $this->mergeExtensionConfig('htmlable', $field, $config, [
                    'field' => $field,
                    'tags' => $this->getStringsAttribute($data, 'tags'),
                    'charset' => $this->getStringAttribute($data, 'charset', 'UTF-8'),
                ]);
            }
        }
    }
}
