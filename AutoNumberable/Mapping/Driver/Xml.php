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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Xml as BaseXml;

/**
 * The xml mapping driver for auto numberable behavioral extension.
 * Used for extraction of extended metadata from xml
 * specifically for AutoNumberable extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Xml extends BaseXml
{
    /**
     * {@inheritdoc}
     *
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
     * Read the field auto numberable configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readFieldMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXmlElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'auto-numberable'})) {
            $this->validateElementType($meta, $mapping, 'auto-numberable', ['field', 'mapped-superclass', 'entity']);

            foreach ($ciMappings->{'auto-numberable'} as $data) {
                $field = $this->getFieldName($meta, $mapping, $data, false, 'auto-numberable');
                $this->validateField($meta, $field, ['string', 'text']);

                $this->mergeExtensionConfig('autoNumberable', $field, $config, [
                    'field' => $field,
                    'pattern' => $this->getStringAttribute($data, 'pattern'),
                    'utc' => $this->getBooleanAttribute($data, 'utc', false),
                    'condition' => $this->getStringAttribute($data, 'condition'),
                ]);
            }
        }
    }
}
