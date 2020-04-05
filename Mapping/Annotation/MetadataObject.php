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
 * @Target("CLASS")
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class MetadataObject extends Annotation
{
    /**
     * @var null|string
     */
    public $name;

    /**
     * @var null|string
     */
    public $pluralName;

    /**
     * @var null|string
     */
    public $type;

    /**
     * @var null|string
     */
    public $fieldIdentifier;

    /**
     * @var null|string
     */
    public $fieldLabel;

    /**
     * @var null|string
     */
    public $label;

    /**
     * @var null|string
     */
    public $description;

    /**
     * @var null|string
     */
    public $translationDomain;

    /**
     * @var null|bool
     */
    public $public = true;

    /**
     * @var null|bool
     */
    public $multiSortable;

    /**
     * @var null|string
     */
    public $defaultSortable;

    /**
     * @var null|string
     */
    public $availableContexts;

    /**
     * @var null|string
     */
    public $formType;

    /**
     * @var array
     */
    public $formOptions = [];

    /**
     * @var string[]
     */
    public $groups = [];

    /**
     * @var null|bool
     */
    public $buildDefaultActions;

    /**
     * @var null|MetadataAction
     */
    public $defaultAction;

    /**
     * @var MetadataAction[]
     */
    public $actions = [];

    /**
     * @var MetadataField[]
     */
    public $fields = [];

    /**
     * @var MetadataAssociation[]
     */
    public $associations = [];
}
