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
 * Auto numberable annotation for AutoNumberable behavioral extension.
 *
 * @Annotation
 *
 * @Target("PROPERTY")
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class AutoNumberable extends Annotation
{
    /**
     * @var array|string
     */
    public $field;

    /**
     * @Annotation\Required
     */
    public ?string $pattern = null;

    public bool $utc = false;

    /**
     * The condition defined by the expression language to generate the number.
     * The expression must return a boolean value.
     */
    public ?string $condition = null;
}
