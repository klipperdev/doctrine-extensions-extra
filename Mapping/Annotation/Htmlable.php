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
 * Htmlable annotation for Htmlable behavioral extension.
 *
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Htmlable extends Annotation
{
    /**
     * @var array|string
     */
    public $field;

    /**
     * @var string[]
     */
    public array $tags = [];

    public string $charset = 'UTF-8';
}
