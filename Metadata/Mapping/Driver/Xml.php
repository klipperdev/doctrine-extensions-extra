<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Metadata\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Xml as BaseXml;
use Klipper\Component\Metadata\Util\MetadataUtil;

/**
 * The xml mapping driver for metadata behavioral extension.
 * Used for extraction of extended metadata from xml
 * specifically for Metadata extension.
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
        $this->readMetadataMapping($meta, $config, $mapping);
        $this->readActionMetadataMapping($meta, $config, $mapping);
        $this->readFieldMetadataMapping($meta, $config, $mapping);
        $this->readAssociationMetadataMapping($meta, $config, $mapping);

        if (isset($mapping->action)) {
            /** @var \SimpleXmlElement $actionMapping */
            foreach ($mapping->action as $actionMapping) {
                $this->readActionMetadataMapping($meta, $config, $actionMapping);
            }
        }

        if (isset($mapping->field)) {
            /** @var \SimpleXmlElement $fieldMapping */
            foreach ($mapping->field as $fieldMapping) {
                $this->readFieldMetadataMapping($meta, $config, $fieldMapping);
            }
        }

        if (isset($mapping->{'one-to-one'})) {
            /** @var \SimpleXmlElement $associationMapping */
            foreach ($mapping->{'one-to-one'} as $associationMapping) {
                $this->readAssociationMetadataMapping($meta, $config, $associationMapping);
            }
        }

        if (isset($mapping->{'one-to-many'})) {
            /** @var \SimpleXmlElement $associationMapping */
            foreach ($mapping->{'one-to-many'} as $associationMapping) {
                $this->readAssociationMetadataMapping($meta, $config, $associationMapping);
            }
        }

        if (isset($mapping->{'many-to-one'})) {
            /** @var \SimpleXmlElement $associationMapping */
            foreach ($mapping->{'many-to-one'} as $associationMapping) {
                $this->readAssociationMetadataMapping($meta, $config, $associationMapping);
            }
        }

        if (isset($mapping->{'many-to-many'})) {
            /** @var \SimpleXmlElement $associationMapping */
            foreach ($mapping->{'many-to-many'} as $associationMapping) {
                $this->readAssociationMetadataMapping($meta, $config, $associationMapping);
            }
        }
    }

    /**
     * Read the metadata configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXMLElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'metadata-object'})) {
            $this->validateElementType($meta, $mapping, 'object metadata', ['mapped-superclass', 'entity']);

            foreach ($ciMappings->{'metadata-object'} as $data) {
                $this->mergeExtensionConfig('metadataObject', null, $config, [
                    'name' => $this->getStringAttribute($data, 'name'),
                    'plural_name' => $this->getStringAttribute($data, 'plural-name'),
                    'type' => $this->getStringAttribute($data, 'type'),
                    'field_identifier' => $this->getStringAttribute($data, 'field-identifier'),
                    'field_label' => $this->getStringAttribute($data, 'field-label'),
                    'label' => $this->getStringAttribute($data, 'label'),
                    'description' => $this->getStringAttribute($data, 'description'),
                    'translation_domain' => $this->getStringAttribute($data, 'translation-domain'),
                    'public' => $this->getBooleanAttribute($data, 'public', true),
                    'multi_sortable' => $this->getBooleanAttribute($data, 'multi-sortable'),
                    'default_sortable' => MetadataUtil::getDefaultSortable($this->getStringAttribute($data, 'default-sortable')),
                    'available_contexts' => MetadataUtil::getStringList($this->getStringAttribute($data, 'available-contexts')),
                    'form_type' => $this->getStringAttribute($data, 'form-type'),
                    'form_options' => $this->getArrayAttribute($data, 'form-options', []),
                    'deep_search_paths' => $this->getArrayAttribute($data, 'deep-search-paths', []),
                    'groups' => $this->getArrayAttribute($data, 'groups', []),
                    'build_default_actions' => $this->getBooleanAttribute($data, 'build-default-actions'),
                    'excluded_default_actions' => $this->getArrayAttribute($data, 'excluded-default-actions', []),
                ]);

                $defaultAction = $this->_isAttributeSet($data, 'default-action') ? $data->attributes()['default-action'] : null;

                foreach ($defaultAction as $defAct) {
                    $this->configureActionMetadata($config, $defAct, 'metadataDefaultAction', null);
                }
            }
        }
    }

    /**
     * Read the action metadata configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readActionMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXMLElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'metadata-action'})) {
            $this->validateElementType($meta, $mapping, 'action metadata', ['mapped-superclass', 'entity']);

            foreach ($ciMappings->{'metadata-action'} as $data) {
                $action = $this->getName($meta, $data, 'action', 'name');

                $this->configureActionMetadata($config, $data, 'metadataActions', $action);
            }
        }
    }

    /**
     * Configure the action metadata.
     *
     * @param array             $config        The config of metadata
     * @param \SimpleXMLElement $data          The action data
     * @param string            $extensionType The extension type
     * @param null|string       $action        The action name
     */
    protected function configureActionMetadata(array &$config, \SimpleXMLElement $data, string $extensionType, ?string $action): void
    {
        $this->mergeExtensionConfig($extensionType, $action, $config, [
            'name' => $action,
            'methods' => $this->getArrayAttribute($data, 'methods'),
            'schemes' => $this->getArrayAttribute($data, 'schemes'),
            'host' => $this->getStringAttribute($data, 'host'),
            'path' => $this->getStringAttribute($data, 'path'),
            'fragment' => $this->getStringAttribute($data, 'fragment'),
            'defaults' => $this->getArrayAttribute($data, 'defaults'),
            'requirements' => $this->getArrayAttribute($data, 'requirements'),
            'options' => $this->getArrayAttribute($data, 'options'),
            'condition' => $this->getStringAttribute($data, 'condition'),
            'configurations' => $this->getArrayAttribute($data, 'configurations'),
        ]);
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readFieldMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXMLElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'metadata-field'})) {
            $this->validateElementType($meta, $mapping, 'field metadata', ['field', 'mapped-superclass', 'entity']);

            foreach ($ciMappings->{'metadata-field'} as $data) {
                $field = $this->getFieldName($meta, $mapping, $data, false, 'field metadata');

                $this->mergeExtensionConfig('metadataFields', $field, $config, [
                    'field' => $field,
                    'name' => $this->getStringAttribute($data, 'name'),
                    'type' => $this->getStringAttribute($data, 'type'),
                    'label' => $this->getStringAttribute($data, 'label'),
                    'description' => $this->getStringAttribute($data, 'description'),
                    'translation_domain' => $this->getStringAttribute($data, 'translation-domain'),
                    'public' => $this->getBooleanAttribute($data, 'public', true),
                    'sortable' => $this->getBooleanAttribute($data, 'sortable'),
                    'filterable' => $this->getBooleanAttribute($data, 'filterable'),
                    'searchable' => $this->getBooleanAttribute($data, 'searchable'),
                    'translatable' => $this->getBooleanAttribute($data, 'translatable'),
                    'read_only' => $this->getBooleanAttribute($data, 'read-only'),
                    'required' => $this->getBooleanAttribute($data, 'required'),
                    'input' => $this->getStringAttribute($data, 'input'),
                    'input_config' => $this->getArrayAttribute($data, 'input-config'),
                    'form_type' => $this->getStringAttribute($data, 'form-type'),
                    'form_options' => $this->getArrayAttribute($data, 'form-options', []),
                    'groups' => $this->getArrayAttribute($data, 'groups', []),
                ]);

                if (!$meta->hasField($field)) {
                    $config['metadataFields'][$field] = array_merge($config['metadataFields'][$field], [
                        'read_only' => true,
                        'sortable' => false,
                        'filterable' => false,
                        'searchable' => false,
                        'translatable' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Read the association metadata configuration in mapping.
     *
     * @param ClassMetadata     $meta    The class metadata
     * @param array             $config  The config of Metadata
     * @param \SimpleXmlElement $mapping The mapping
     */
    protected function readAssociationMetadataMapping(ClassMetadata $meta, array &$config, \SimpleXMLElement $mapping): void
    {
        $ciMappings = $mapping->children(self::KLIPPER_NAMESPACE_URI);

        if (isset($ciMappings->{'metadata-association'})) {
            $this->validateElementType($meta, $mapping, 'association metadata', ['one-to-one', 'one-to-many', 'many-to-one', 'many-to-many', 'mapped-superclass', 'entity']);

            foreach ($ciMappings->{'metadata-association'} as $data) {
                $field = $this->getFieldName($meta, $mapping, $data, true, 'association metadata');

                $this->mergeExtensionConfig('metadataAssociations', $field, $config, [
                    'association' => $field,
                    'name' => $this->getStringAttribute($data, 'name'),
                    'type' => $this->getStringAttribute($data, 'type'),
                    'target' => $this->getStringAttribute($data, 'target'),
                    'label' => $this->getStringAttribute($data, 'label'),
                    'description' => $this->getStringAttribute($data, 'description'),
                    'translation_domain' => $this->getStringAttribute($data, 'translation-domain'),
                    'public' => $this->getBooleanAttribute($data, 'public', true),
                    'required' => $this->getBooleanAttribute($data, 'required'),
                    'input' => $this->getStringAttribute($data, 'input'),
                    'input_config' => $this->getArrayAttribute($data, 'input-config'),
                    'form_type' => $this->getStringAttribute($data, 'form-type'),
                    'form_options' => $this->getArrayAttribute($data, 'form-options', []),
                    'groups' => $this->getArrayAttribute($data, 'groups', []),
                ]);
            }
        }
    }
}
