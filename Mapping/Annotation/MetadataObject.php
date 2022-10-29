<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Metadata object annotation for Metadata behavioral extension.
 *
 * @Annotation
 *
 * @Target("CLASS")
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class MetadataObject extends Annotation
{
    public ?string $name = null;

    public ?string $pluralName = null;

    public ?string $type = null;

    public ?string $fieldIdentifier = null;

    public ?string $fieldLabel = null;

    public ?string $label = null;

    public ?string $description = null;

    public ?string $translationDomain = null;

    public bool $public = true;

    public ?bool $multiSortable = null;

    public ?string $defaultSortable = null;

    public ?array $availableContexts = null;

    public ?string $formType = null;

    public array $formOptions = [];

    /**
     * @var string[]
     */
    public array $deepSearchPaths = [];

    /**
     * @var string[]
     */
    public array $groups = [];

    public ?bool $buildDefaultActions = null;

    /**
     * @var string[]
     */
    public array $excludedDefaultActions = [];

    public ?MetadataAction $defaultAction = null;

    /**
     * @var MetadataAction[]
     */
    public array $actions = [];

    /**
     * @var MetadataField[]
     */
    public array $fields = [];

    /**
     * @var MetadataAssociation[]
     */
    public array $associations = [];
}
