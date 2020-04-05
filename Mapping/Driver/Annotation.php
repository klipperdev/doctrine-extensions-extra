<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

/**
 * The mapping AnnotationDriver abstract class, defines the
 * metadata extraction function common among all
 * all drivers used on these extensions by file based
 * drivers.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class Annotation extends AbstractAnnotationDriver
{
    use MergeConfigTrait;
}
