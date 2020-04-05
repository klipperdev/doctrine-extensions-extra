<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Loader;

use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Metadata\ClassMetadataInterface;

/**
 * The mapping loader of default value.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface LoaderInterface
{
    /**
     * Load the the class metadatas of default value.
     *
     * @return ClassMetadataInterface[]
     */
    public function load(): array;
}
