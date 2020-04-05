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
 * @Target({"CLASS", "ANNOTATION"})
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class MetadataAction extends Annotation
{
    /**
     * @var null|string
     */
    public $name;

    /**
     * @var string[]
     */
    public $methods = [];

    /**
     * @var string[]
     */
    public $schemes = [];

    /**
     * @var null|string
     */
    public $host;

    /**
     * @var null|string
     */
    public $path;

    /**
     * @var null|string
     */
    public $fragment;

    /**
     * @var array
     */
    public $defaults = [];

    /**
     * @var array
     */
    public $requirements = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var null|string
     */
    public $condition;

    /**
     * @var array
     */
    public $configurations = [];
}
