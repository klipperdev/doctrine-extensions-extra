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
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidMappingException;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\MetadataAction;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\MetadataAssociation;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\MetadataField;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation\MetadataObject;
use Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver\Annotation as BaseAnnotation;
use Klipper\Component\Metadata\Util\MetadataUtil;

/**
 * The annotation mapping driver for metadata behavioral extension.
 * Used for extraction of extended metadata from Annotations
 * specifically for Metadata extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Annotation extends BaseAnnotation
{
    /**
     * Annotation is metadata field.
     */
    public const METADATA_FIELD = MetadataField::class;

    /**
     * Annotation is metadata association.
     */
    public const METADATA_ASSOCIATION = MetadataAssociation::class;

    /**
     * @param ClassMetadata $meta
     */
    public function readExtendedMetadata($meta, array &$config): void
    {
        $class = $this->getMetaReflectionClass($meta);

        $this->readMetadataMapping($meta, $config, $class);

        foreach ($class->getProperties() as $property) {
            $this->readPropertyMetadataMapping($meta, $config, $property);
        }

        foreach ($class->getMethods() as $method) {
            $this->readMethodMetadataMapping($meta, $config, $method);
        }
    }

    /**
     * Read the metadata configuration in mapping.
     *
     * @param ClassMetadata    $meta   The class metadata
     * @param array            $config The config of Metadata
     * @param \ReflectionClass $class  The reflection class
     */
    protected function readMetadataMapping(ClassMetadata $meta, array &$config, \ReflectionClass $class): void
    {
        foreach ($this->reader->getClassAnnotations($class) as $data) {
            if ($data instanceof MetadataObject) {
                $this->mergeExtensionConfig('metadataObject', null, $config, [
                    'name' => $data->name,
                    'plural_name' => $data->pluralName,
                    'type' => $data->type,
                    'field_identifier' => $data->fieldIdentifier,
                    'field_label' => $data->fieldLabel,
                    'label' => $data->label,
                    'description' => $data->description,
                    'translation_domain' => $data->translationDomain,
                    'public' => $data->public ?? true,
                    'multi_sortable' => $data->multiSortable,
                    'default_sortable' => MetadataUtil::getDefaultSortable($data->defaultSortable),
                    'available_contexts' => MetadataUtil::getStringList($data->availableContexts),
                    'form_type' => $data->formType,
                    'form_options' => $data->formOptions,
                    'groups' => $data->groups,
                    'build_default_actions' => $data->buildDefaultActions,
                ]);

                if ($data->defaultAction instanceof MetadataAction) {
                    $this->configureActionMetadata($config, $data->defaultAction, 'metadataDefaultAction', null);
                }

                foreach ($data->actions as $action => $actionData) {
                    $this->readActionMetadataMapping($meta, $config, $actionData, $action);
                }

                foreach ($data->fields as $field => $fieldData) {
                    $this->readFieldMetadataMapping($meta, $config, $fieldData, $field);
                }

                foreach ($data->associations as $association => $associationData) {
                    $this->readAssociationMetadataMapping($meta, $config, $associationData, $association);
                }
            } elseif ($data instanceof MetadataAction) {
                if (null === $data->name) {
                    throw new InvalidMappingException("Metadata Action - The 'name' property is required when the annotation is used on the class - {$meta->getName()}");
                }

                $this->readActionMetadataMapping($meta, $config, $data, $data->name);
            } elseif ($data instanceof MetadataField) {
                if (null === $data->field) {
                    throw new InvalidMappingException("Metadata Field - The 'field' property is required when the annotation is used on the class - {$meta->getName()}");
                }

                $this->readFieldMetadataMapping($meta, $config, $data, $data->field);
            } elseif ($data instanceof MetadataAssociation) {
                if (null === $data->association) {
                    throw new InvalidMappingException("Metadata Association - The 'association' property is required when the annotation is used on the class - {$meta->getName()}");
                }

                $this->readAssociationMetadataMapping($meta, $config, $data, $data->association);
            }
        }
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata       $meta     The class metadata
     * @param array               $config   The config of Metadata
     * @param \ReflectionProperty $property The reflection property
     */
    protected function readPropertyMetadataMapping(ClassMetadata $meta, array &$config, \ReflectionProperty $property): void
    {
        if ($data = $this->reader->getPropertyAnnotation($property, self::METADATA_FIELD)) {
            /* @var MetadataField $data */
            $this->readFieldMetadataMapping($meta, $config, $data, $property->getName());
        } elseif ($data = $this->reader->getPropertyAnnotation($property, self::METADATA_ASSOCIATION)) {
            /* @var MetadataAssociation $data */
            $this->readAssociationMetadataMapping($meta, $config, $data, $property->getName());
        }
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata     $meta   The class metadata
     * @param array             $config The config of Metadata
     * @param \ReflectionMethod $method The reflection method
     */
    protected function readMethodMetadataMapping(ClassMetadata $meta, array &$config, \ReflectionMethod $method): void
    {
        if ($data = $this->reader->getMethodAnnotation($method, self::METADATA_FIELD)) {
            /* @var MetadataField $data */
            $this->readFieldMetadataMapping($meta, $config, $data, $method->getName().'()');
        } elseif ($data = $this->reader->getMethodAnnotation($method, self::METADATA_ASSOCIATION)) {
            /* @var MetadataAssociation $data */
            $this->readAssociationMetadataMapping($meta, $config, $data, $method->getName().'()');
        }
    }

    /**
     * Read the action metadata configuration in mapping.
     *
     * @param ClassMetadata  $meta   The class metadata
     * @param array          $config The config of Metadata
     * @param MetadataAction $data   The metadata action
     * @param string         $action The action name
     */
    protected function readActionMetadataMapping(ClassMetadata $meta, array &$config, MetadataAction $data, string $action): void
    {
        $action = $data->name ?? $action;

        if (null === $action) {
            throw new InvalidMappingException("Metadata Action - The name property is required for the metadata action in the {$meta->getName()} class");
        }

        $this->configureActionMetadata($config, $data, 'metadataActions', $action);
    }

    /**
     * @param array          $config        The config of metadata
     * @param MetadataAction $data          The metadata action
     * @param string         $extensionType The extension type
     * @param null|string    $action        The action name
     */
    protected function configureActionMetadata(array &$config, MetadataAction $data, string $extensionType, ?string $action): void
    {
        $this->mergeExtensionConfig($extensionType, $action, $config, [
            'name' => $data->name,
            'methods' => !empty($data->methods) ? $data->methods : null,
            'schemes' => !empty($data->schemes) ? $data->schemes : null,
            'host' => $data->host,
            'path' => $data->path,
            'fragment' => $data->fragment,
            'defaults' => !empty($data->defaults) ? $data->defaults : null,
            'requirements' => !empty($data->requirements) ? $data->requirements : null,
            'options' => !empty($data->options) ? $data->options : null,
            'condition' => $data->condition,
            'configurations' => $data->configurations,
        ]);
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata $meta   The class metadata
     * @param array         $config The config of Metadata
     * @param MetadataField $data   The metadata field
     * @param string        $field  The field name
     */
    protected function readFieldMetadataMapping(ClassMetadata $meta, array &$config, MetadataField $data, $field): void
    {
        if (null === $data->field && !$meta->hasField($field)) {
            throw new InvalidMappingException("Metadata Field - [{$field}] property is not a field in doctrine class - {$meta->getName()}");
        }

        $field = $data->field ?? $field;

        if (!$meta->hasField($field)) {
            $data->readOnly = true;
            $data->filterable = false;
            $data->searchable = false;
            $data->sortable = false;
            $data->translatable = false;
        }

        $this->mergeExtensionConfig('metadataFields', $field, $config, [
            'field' => $field,
            'name' => $data->name,
            'type' => $data->type,
            'label' => $data->label,
            'description' => $data->description,
            'translation_domain' => $data->translationDomain,
            'public' => $data->public ?? true,
            'sortable' => $data->sortable,
            'filterable' => $data->filterable,
            'searchable' => $data->searchable,
            'translatable' => $data->translatable,
            'read_only' => $data->readOnly,
            'required' => $data->required,
            'input' => $data->input,
            'input_config' => !empty($data->inputConfig) ? $data->inputConfig : null,
            'form_type' => $data->formType,
            'form_options' => $data->formOptions,
            'groups' => $data->groups,
        ]);
    }

    /**
     * Read the field metadata configuration in mapping.
     *
     * @param ClassMetadata       $meta        The class metadata
     * @param array               $config      The config of Metadata
     * @param MetadataAssociation $data        The metadata association
     * @param string              $association The association name
     */
    protected function readAssociationMetadataMapping(ClassMetadata $meta, array &$config, MetadataAssociation $data, $association): void
    {
        if (null === $data->association && !$meta->hasAssociation($association)) {
            throw new InvalidMappingException("Metadata Association - [{$association}] property is not a association in doctrine class - {$meta->getName()}");
        }

        $association = $data->association ?? $association;

        $this->mergeExtensionConfig('metadataAssociations', $association, $config, [
            'association' => $association,
            'name' => $data->name,
            'type' => $data->type,
            'target' => $data->target,
            'label' => $data->label,
            'description' => $data->description,
            'translation_domain' => $data->translationDomain,
            'public' => $data->public ?? true,
            'required' => $data->required,
            'input' => $data->input,
            'input_config' => !empty($data->inputConfig) ? $data->inputConfig : null,
            'form_type' => $data->formType,
            'form_options' => $data->formOptions,
            'groups' => $data->groups,
        ]);
    }
}
