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
use Klipper\Component\Metadata\ObjectMetadataInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterConfigBuilder extends FilterConfig
{
    /**
     * @var ObjectMetadataInterface
     */
    protected $initialMeta;

    /**
     * @var ObjectMetadataInterface
     */
    protected $currentMeta;

    /**
     * @var string[]
     */
    protected $initialTargets;

    /**
     * @var string
     */
    protected $filterPrefix = '';

    /**
     * @var string
     */
    protected $groupPrefix = '';

    /**
     * @var string
     */
    protected $groupLabel = '';

    /**
     * @var null|AssociationMetadataInterface
     */
    protected $previousAssociation;

    /**
     * Constructor.
     *
     * @param ObjectMetadataInterface $initialMeta The initial object metadata
     */
    public function __construct(ObjectMetadataInterface $initialMeta)
    {
        parent::__construct([], []);

        $this->initialMeta = $initialMeta;
        $this->currentMeta = $initialMeta;

        foreach ($initialMeta->getAssociations() as $associationMetadata) {
            $this->initialTargets[] = $associationMetadata->getTarget();
        }
    }

    /**
     * Get the initial object metadata.
     */
    public function getInitialObjectMetadata(): ObjectMetadataInterface
    {
        return $this->initialMeta;
    }

    /**
     * Get the metadata name of all initial association targets.
     *
     * @return string[]
     */
    public function getInitialTargets(): array
    {
        return $this->initialTargets;
    }

    /**
     * Set the current object metadata.
     *
     * @param ObjectMetadataInterface $meta The current object metadata
     *
     * @return static
     */
    public function setObjectMetadata(ObjectMetadataInterface $meta): self
    {
        $this->currentMeta = $meta;

        return $this;
    }

    /**
     * Get the current object metadata.
     */
    public function getObjectMetadata(): ObjectMetadataInterface
    {
        return $this->currentMeta;
    }

    /**
     * Set the filters.
     *
     * @param array $filters The filters
     *
     * @return static
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Add the filter.
     *
     * @param array $filter The filter
     *
     * @return static
     */
    public function addFilter(array $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Set the filter prefix.
     *
     * @param string $prefix The filter prefix
     *
     * @return static
     */
    public function setFilterPrefix(string $prefix): self
    {
        $this->filterPrefix = $prefix;

        return $this;
    }

    /**
     * Get the filter prefix.
     */
    public function getFilterPrefix(): string
    {
        return $this->filterPrefix;
    }

    /**
     * Set the group prefix.
     *
     * @param string $group The group prefix
     *
     * @return static
     */
    public function setGroupPrefix(string $group): self
    {
        $this->groupPrefix = $group;

        return $this;
    }

    /**
     * Get the group prefix.
     */
    public function getGroupPrefix(): string
    {
        return $this->groupPrefix;
    }

    /**
     * Set the group label.
     *
     * @param string $label The group label
     *
     * @return static
     */
    public function setGroupLabel(string $label): self
    {
        $this->groupLabel = $label;

        return $this;
    }

    /**
     * Get the group label.
     */
    public function getGroupLabel(): string
    {
        return $this->groupLabel;
    }

    /**
     * Set the filter groups.
     *
     * @param array $groups The filter groups
     *
     * @return static
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Add the filter group.
     *
     * @param string $id    The filter group id
     * @param string $label The filter group label
     *
     * @return static
     */
    public function addGroup(string $id, string $label): self
    {
        $this->groups[$id] = $label;

        return $this;
    }

    /**
     * Set the previous association.
     *
     * @param null|AssociationMetadataInterface $previousAssociation The previous association metadata
     *
     * @return static
     */
    public function setPreviousAssociation(?AssociationMetadataInterface $previousAssociation): self
    {
        $this->previousAssociation = $previousAssociation;

        return $this;
    }

    /**
     * Get the previous association.
     */
    public function getPreviousAssociation(): ?AssociationMetadataInterface
    {
        return $this->previousAssociation;
    }

    /**
     * Get the filters.
     */
    public function getFilters(): array
    {
        $filters = $this->filters;

        usort($filters, static function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $filters;
    }
}
