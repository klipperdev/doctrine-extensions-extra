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
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;

/**
 * Interface of node transformer.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NodeTransformerInterface
{
    /**
     * Compile the node in Doctrine Query Language.
     *
     * @param CompileArgs $arguments The arguments used for compilation
     * @param RuleNode    $node      The rule node
     */
    public function compile(CompileArgs $arguments, RuleNode $node): string;
}
