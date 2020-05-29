<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable;

use Klipper\Component\Metadata\AssociationMetadataInterface;
use Klipper\Component\Metadata\ChoiceInterface;
use Klipper\Component\Metadata\FieldMetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Klipper\Component\Metadata\Util\ChoiceUtil;
use Klipper\Component\Metadata\Util\MetadataUtil;
use Klipper\Component\Security\Permission\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterManager implements FilterManagerInterface
{
    public const DEPTH_MAX = 10;

    public const TYPE_MAPPING = [
        'array' => 'array',
        'blob' => 'string',
        'boolean' => 'boolean',
        'date' => 'date',
        'datetime' => 'datetime',
        'number' => 'double',
        'object' => 'object',
        'time' => 'time',
        'uuid' => 'string',
    ];

    public const INPUT_MAPPING = [
        'string' => 'text',
        'array' => 'choice',
        'object' => 'object',
        'date' => 'date',
        'datetime' => 'datetime',
        'time' => 'time',
        'number' => 'number',
        'blob' => 'text',
        'boolean' => 'checkbox',
        'uuid' => 'text',
    ];

    public const SKIP_INPUTS = [
        'point',
    ];

    /**
     * @var MetadataManagerInterface
     */
    protected $metadataManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param MetadataManagerInterface      $metadataManager The permission metadata manager
     * @param AuthorizationCheckerInterface $authChecker     The authorization checker
     * @param TranslatorInterface           $translator      The translator
     */
    public function __construct(
        MetadataManagerInterface $metadataManager,
        AuthorizationCheckerInterface $authChecker,
        TranslatorInterface $translator
    ) {
        $this->metadataManager = $metadataManager;
        $this->authChecker = $authChecker;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(string $class): FilterConfig
    {
        return $this->metadataManager->has($class)
            ? $this->doGetFilters($this->metadataManager->get($class))
            : new FilterConfig([], []);
    }

    /**
     * {@inheritdoc}
     */
    public function getFiltersByName(string $name): FilterConfig
    {
        return $this->metadataManager->hasByName($name)
            ? $this->doGetFilters($this->metadataManager->getByName($name))
            : new FilterConfig([], []);
    }

    /**
     * Get the filters by object metadata.
     *
     * @param ObjectMetadataInterface $meta The object metadata
     */
    public function doGetFilters(ObjectMetadataInterface $meta): FilterConfig
    {
        $builder = $this->findFilters(new FilterConfigBuilder($meta));

        return new FilterConfig($builder->getFilters(), $builder->getGroups());
    }

    /**
     * @param FilterConfigBuilder $builder The filter config builder
     * @param int                 $depth   The depth of association children
     */
    protected function findFilters(FilterConfigBuilder $builder, int $depth = 0): FilterConfigBuilder
    {
        $meta = $builder->getObjectMetadata();

        if ($depth <= static::DEPTH_MAX && $this->isObjectFilterable($meta)) {
            $filterPrefix = $builder->getFilterPrefix();
            $groupPrefix = $builder->getGroupPrefix();
            $groupLabel = $builder->getGroupLabel();

            if (empty($groupPrefix)) {
                $groupName = $meta->getName();
                $groupPrefix = $groupName;
                $builder->addGroup($groupName, MetadataUtil::getTrans($this->translator, $meta->getLabel(), $meta->getTranslationDomain()));
            } else {
                $groupName = $groupPrefix;
            }

            foreach ($meta->getFields() as $fieldMeta) {
                if ($this->isFieldFilterable($fieldMeta)) {
                    $filter = [
                        'id' => $filterPrefix.$fieldMeta->getName(),
                        'label' => MetadataUtil::getTrans($this->translator, $fieldMeta->getLabel(), $fieldMeta->getTranslationDomain()),
                        'type' => $this->getType($fieldMeta),
                        'optgroup' => $groupName,
                        'input' => $this->getInput($fieldMeta),
                    ];

                    $filter = $this->configureFieldFilter($fieldMeta, $filter);
                    $builder->addFilter($filter);
                }
            }

            foreach ($meta->getAssociations() as $associationMetadata) {
                if ($this->isAssociationFilterable($associationMetadata, $builder)) {
                    $assObjectMeta = $this->metadataManager->getByName($associationMetadata->getTarget());

                    if (empty($groupLabel)) {
                        $newLabel = MetadataUtil::getTrans($this->translator, $assObjectMeta->getLabel(), $assObjectMeta->getTranslationDomain());
                    } else {
                        $newLabel = $groupLabel.' / '.MetadataUtil::getTrans($this->translator, $associationMetadata->getLabel(), $associationMetadata->getTranslationDomain());
                    }

                    $builder->setObjectMetadata($assObjectMeta);
                    $builder->addGroup(ltrim($groupPrefix.'.'.$associationMetadata->getName(), '.'), $newLabel);
                    $builder->setFilterPrefix(ltrim($filterPrefix.$associationMetadata->getName().'.', '.'));
                    $builder->setGroupPrefix(ltrim($groupPrefix.'.'.$associationMetadata->getName(), '.'));
                    $builder->setGroupLabel($newLabel);
                    $builder->setPreviousAssociation($associationMetadata);
                    $builder = $this->findFilters($builder, $depth++);
                    $builder->setObjectMetadata($meta);
                    $builder->setFilterPrefix($filterPrefix);
                    $builder->setGroupPrefix($groupPrefix);
                    $builder->setGroupLabel($groupLabel);
                    $builder->setPreviousAssociation(null);
                }
            }
        }

        return $builder;
    }

    /**
     * Configure the filter.
     *
     * @param FieldMetadataInterface $meta   The field metadata
     * @param array                  $filter The array of filter
     */
    protected function configureFieldFilter(FieldMetadataInterface $meta, array $filter): array
    {
        if (null !== $inputConfig = $meta->getInputConfig()) {
            if (\array_key_exists('multiple', $inputConfig)) {
                $filter['multiple'] = $inputConfig['multiple'];
                unset($inputConfig['multiple']);
            }

            if (isset($inputConfig['choices']) && false !== strpos($inputConfig['choices'], '#/choices/')) {
                $choice = $choice = $this->metadataManager->getChoice(substr($inputConfig['choices'], 10));
                $filter['values'] = $this->getChoiceValues($choice);
                unset($inputConfig['choices']);
                $placeholder = ChoiceUtil::getTransPlaceholder($this->translator, $choice->getPlaceholder(), $choice->getTranslationDomain());

                if (null !== $placeholder) {
                    $filter['placeholder'] = $placeholder;
                }
            }
        }

        if (!empty($inputConfig)) {
            $filter['input_config'] = $inputConfig;
        }

        return $filter;
    }

    /**
     * Get the value of the choice.
     *
     * @param ChoiceInterface $choice The choice
     */
    protected function getChoiceValues(ChoiceInterface $choice): array
    {
        $translationDomain = $choice->getTranslationDomain();
        $identifiers = ChoiceUtil::getTrans($this->translator, $choice->getListIdentifiers(), $translationDomain);
        $values = [];

        foreach ($identifiers as $key => $value) {
            if (\is_array($value)) {
                foreach ($value as $groupKey => $groupValue) {
                    $values[] = [
                        'value' => $groupKey,
                        'label' => $groupValue,
                        'optgroup' => $key,
                    ];
                }
            } else {
                $values[] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $values;
    }

    /**
     * Get the filter type.
     *
     * @param FieldMetadataInterface $meta The field metadata
     */
    protected function getType(FieldMetadataInterface $meta): string
    {
        $type = $meta->getType();
        $inputConfig = $meta->getInputConfig();

        if ('number' === $type && \array_key_exists('scale', $inputConfig) && 0 === $inputConfig['scale']) {
            return 'integer';
        }

        return self::TYPE_MAPPING[$type] ?? 'string';
    }

    /**
     * Get the filter input.
     *
     * @param FieldMetadataInterface $meta The field metadata
     */
    protected function getInput(FieldMetadataInterface $meta): string
    {
        $input = $meta->getInput();

        if (null === $input) {
            $input = self::INPUT_MAPPING[$meta->getType()] ?? null;
        }

        return $input ?? 'text';
    }

    /**
     * Check if the object is filterable.
     *
     * @param ObjectMetadataInterface $metadata The object metadata
     */
    protected function isObjectFilterable(ObjectMetadataInterface $metadata): bool
    {
        return $metadata->isPublic()
            && $metadata->isFilterable()
            && $this->authChecker->isGranted('perm:view', $metadata->getClass());
    }

    /**
     * Check if the field is filterable.
     *
     * @param FieldMetadataInterface $fieldMetadata The field metadata
     */
    protected function isFieldFilterable(FieldMetadataInterface $fieldMetadata): bool
    {
        return $fieldMetadata->isPublic()
            && $fieldMetadata->isFilterable()
            && !\in_array($fieldMetadata->getType(), self::SKIP_INPUTS, true)
            && $this->authChecker->isGranted('perm:read', new FieldVote($fieldMetadata->getParent()->getClass(), $fieldMetadata->getField()));
    }

    /**
     * Check if the association is filterable.
     *
     * @param AssociationMetadataInterface $associationMetadata The association metadata
     * @param FilterConfigBuilder          $builder             The filter config builder
     */
    protected function isAssociationFilterable(AssociationMetadataInterface $associationMetadata, FilterConfigBuilder $builder): bool
    {
        return $associationMetadata->isPublic()
            && $this->isValidAssociation($associationMetadata, $builder)
            && $this->authChecker->isGranted('perm:read', new FieldVote($associationMetadata->getParent()->getClass(), $associationMetadata->getAssociation()));
    }

    /**
     * Check if the many to one association is valid.
     *
     * @param AssociationMetadataInterface $associationMetadata The association metadata
     * @param FilterConfigBuilder          $builder             The filter config builder
     */
    protected function isValidAssociation(AssociationMetadataInterface $associationMetadata, FilterConfigBuilder $builder): bool
    {
        $initialMeta = $builder->getInitialObjectMetadata();
        $initialName = $initialMeta->getName();
        $previousAssociation = $builder->getPreviousAssociation();

        if ($initialName !== $associationMetadata->getTarget()
                && \in_array($associationMetadata->getType(), ['many-to-one', 'one-to-one'], true)
                && ($associationMetadata->getParent()->getName() === $initialName || !\in_array($associationMetadata->getTarget(), $builder->getInitialTargets(), true))
        ) {
            if ($previousAssociation instanceof AssociationMetadataInterface) {
                return $associationMetadata->getTarget() !== $previousAssociation->getParent()->getName();
            }

            return true;
        }

        return false;
    }
}
