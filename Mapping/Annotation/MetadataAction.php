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
    public ?string $name = null;

    /**
     * @var string[]
     */
    public array $methods = [];

    /**
     * @var string[]
     */
    public array $schemes = [];

    public ?string $host = null;

    public ?string $path = null;

    public ?string $fragment = null;

    public array $defaults = [];

    public array $requirements = [];

    public array $options = [];

    public ?string $condition = null;

    public array $configurations = [];
}
