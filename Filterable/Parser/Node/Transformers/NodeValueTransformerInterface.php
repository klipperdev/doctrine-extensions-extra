<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;

/**
 * Interface of node transformer of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NodeValueTransformerInterface
{
    /**
     * Compile the node in Doctrine Query Language.
     *
     * @param CompileArgs $arguments The arguments used for compilation
     * @param string      $field     The field name
     */
    public function compileValue(CompileArgs $arguments, string $field): string;
}
