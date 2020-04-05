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
 * Metadata field annotation for Metadata behavioral extension.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "CLASS", "ANNOTATION"})
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class MetadataAssociation extends Annotation
{
    /**
     * @var null|string
     */
    public $association;

    /**
     * @var null|string
     */
    public $name;

    /**
     * @var null|string
     */
    public $type;

    /**
     * @var null|string
     */
    public $target;

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
    public $public;

    /**
     * @var null|bool
     */
    public $readOnly;

    /**
     * @var null|bool
     */
    public $required;

    /**
     * @var null|string
     */
    public $input;

    /**
     * @var null|array
     */
    public $inputConfig = [];

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
}
