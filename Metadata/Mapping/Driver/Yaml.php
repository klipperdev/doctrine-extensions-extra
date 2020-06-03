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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Yaml as BaseYaml;
use Klipper\Component\Metadata\Util\MetadataUtil;

/**
 * The yaml mapping driver for metadata behavioral extension.
 * Used for extraction of extended metadata from yaml
 * specifically for Metadata extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Yaml extends BaseYaml
{
    /**
     * @param ClassMetadata $meta
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        $mapping = $this->_getMapping($meta->getName());

        $this->readMetadataMapping($config, $mapping);
        $this->readActionMetadataMappings($meta, $mapping);
        $this->readFieldMetadataMappings($meta, $mapping);
        $this->readAssociationMetadataMappings($mapping, 'oneToOne');
        $this->readAssociationMetadataMappings($mapping, 'oneToMany');
        $this->readAssociationMetadataMappings($mapping, 'manyToOne');
        $this->readAssociationMetadataMappings($mapping, 'manyToMany');
    }

    /**
     * Read the metadata configuration in mapping.
     *
     * @param array $config  The config of Metadata
     * @param array $mapping The mapping
     */
    protected function readMetadataMapping(array &$config, array $mapping): void
    {
        $data = $this->getMappingData($mapping, 'metadataObject');

        if (null !== $data) {
            $config['metadataObject'] = [
                'name' => $this->getStringAttribute($data, 'name'),
                'plural_name' => $this->getStringAttribute($data, 'pluralName'),
                'type' => $this->getStringAttribute($data, 'type'),
                'label' => $this->getStringAttribute($data, 'label'),
                'field_identifier' => $this->getStringAttribute($data, 'fieldIdentifier'),
                'field_label' => $this->getStringAttribute($data, 'fieldLabel'),
                'description' => $this->getStringAttribute($data, 'description'),
                'translation_domain' => $this->getStringAttribute($data, 'translationDomain'),
                'public' => $this->getBooleanAttribute($data, 'public', true),
                'multi_sortable' => $this->getBooleanAttribute($data, 'sortable'),
                'default_sortable' => MetadataUtil::getDefaultSortable($this->getStringAttribute($data, 'defaultSortable')),
                'available_contexts' => MetadataUtil::getStringList($this->getStringAttribute($data, 'availableContexts')),
                'form_type' => $this->getStringAttribute($data, 'formType'),
                'form_options' => $this->getArrayAttribute($data, 'formOptions', []),
                'groups' => $this->getArrayAttribute($data, 'groups', []),
                'build_default_actions' => $this->getBooleanAttribute($data, 'buildDefaultActions'),
            ];

            if (null !== $defaultAction = $this->getArrayAttribute($data, 'defaultAction')) {
                $config['metadataDefaultAction'] = $this->configureActionMetadata($defaultAction, null);
            }
        }
    }

    /**
     * Read the action metadata configurations in mapping.
     *
     * @param ClassMetadata $meta    The class metadata
     * @param array         $mapping The object mapping
     */
    protected function readActionMetadataMappings(ClassMetadata $meta, array $mapping): void
    {
        if (isset($mapping['actions'])) {
            foreach ($mapping['actions'] as $action => $actionMapping) {
                $this->readActionMetadataMapping($meta, $config, $action, $actionMapping);
            }
        }
    }

    /**
     * Read the action metadata configuration in mapping.
     *
     * @param ClassMetadata $meta    The class metadata
     * @param array         $config  The config of Metadata
     * @param string        $action  The action name
     * @param array         $mapping The mapping
     */
    protected function readActionMetadataMapping(ClassMetadata $meta, array &$config, $action, array $mapping): void
    {
        $data = $this->getMappingData($mapping, 'metadataAction');
        $action = $data['name'] ?? $action;

        if (null !== $data) {
            if (null === $action) {
                throw new InvalidMappingException("Metadata Action - The name property is required for the metadata action in the {$meta->getName()} class");
            }

            $config['metadataActions'][$action] = $this->configureActionMetadata($data, $action);
        }
    }

    /**
     * Configure the action metadata.
     *
     * @param array       $data   The action data
     * @param null|string $action The action name
     */
    protected function configureActionMetadata(array $data, ?string $action): ?array
    {
        if (null === $data) {
            return $data;
        }

        return [
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
        ];
    }

    /**
     * Read the field metadata configurations in mapping.
     *
     * @param ClassMetadata $meta    The class metadata
     * @param array         $mapping The object mapping
     */
    protected function readFieldMetadataMappings(ClassMetadata $meta, array $mapping): void
    {
        if (isset($mapping['fields'])) {
            foreach ($mapping['fields'] as $field => $fieldMapping) {
                $this->readFieldMetadataMapping($meta, $config, $field, $fieldMapping);
            }
        }
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata $meta    The class metadata
     * @param array         $config  The config of Metadata
     * @param string        $field   The field name
     * @param array         $mapping The mapping
     */
    protected function readFieldMetadataMapping(ClassMetadata $meta, array &$config, $field, array $mapping): void
    {
        $data = $this->getMappingData($mapping, 'metadataField');

        if (null !== $data) {
            $config['metadataFields'][$field] = [
                'field' => $field,
                'name' => $this->getStringAttribute($data, 'name'),
                'type' => $this->getStringAttribute($data, 'type'),
                'label' => $this->getStringAttribute($data, 'label'),
                'description' => $this->getStringAttribute($data, 'description'),
                'translation_domain' => $this->getStringAttribute($data, 'translationDomain'),
                'public' => $this->getBooleanAttribute($data, 'public', true),
                'sortable' => $this->getBooleanAttribute($data, 'sortable'),
                'filterable' => $this->getBooleanAttribute($data, 'filterable'),
                'searchable' => $this->getBooleanAttribute($data, 'searchable'),
                'translatable' => $this->getBooleanAttribute($data, 'translatable'),
                'read_only' => $this->getBooleanAttribute($data, 'readOnly'),
                'required' => $this->getBooleanAttribute($data, 'required'),
                'input' => $this->getStringAttribute($data, 'input'),
                'input_config' => $this->getArrayAttribute($data, 'inputConfig'),
                'form_type' => $this->getStringAttribute($data, 'formType'),
                'form_options' => $this->getArrayAttribute($data, 'formOptions', []),
                'groups' => $this->getArrayAttribute($data, 'groups', []),
            ];

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

    /**
     * Read the association metadata configurations in mapping.
     *
     * @param array  $mapping The object mapping
     * @param string $key     The key of association
     */
    protected function readAssociationMetadataMappings(array $mapping, $key): void
    {
        if (isset($mapping[$key])) {
            foreach ($mapping[$key] as $field => $associationMapping) {
                $this->readAssociationMetadataMapping($config, $field, $associationMapping);
            }
        }
    }

    /**
     * Read the association metadata configuration in mapping.
     *
     * @param array  $config  The config of Metadata
     * @param string $field   The field name
     * @param array  $mapping The mapping
     */
    protected function readAssociationMetadataMapping(array &$config, $field, array $mapping): void
    {
        $data = $this->getMappingData($mapping, 'metadataAssociation');

        if (null !== $data) {
            $config['metadataAssociations'][$field] = [
                'association' => $field,
                'name' => $this->getStringAttribute($data, 'name'),
                'type' => $this->getStringAttribute($data, 'type'),
                'target' => $this->getStringAttribute($data, 'target'),
                'label' => $this->getStringAttribute($data, 'label'),
                'description' => $this->getStringAttribute($data, 'description'),
                'translation_domain' => $this->getStringAttribute($data, 'translationDomain'),
                'public' => $this->getBooleanAttribute($data, 'public', true),
                'required' => $this->getBooleanAttribute($data, 'required'),
                'input' => $this->getStringAttribute($data, 'input'),
                'input_config' => $this->getArrayAttribute($data, 'inputConfig'),
                'form_type' => $this->getStringAttribute($data, 'formType'),
                'form_options' => $this->getArrayAttribute($data, 'formOptions', []),
                'groups' => $this->getArrayAttribute($data, 'groups', []),
            ];
        }
    }
}
