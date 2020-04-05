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
 * Interface of node transformer of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NodeFieldNameTransformerInterface
{
    /**
     * Compile the node in Doctrine Query Language.
     *
     * @param CompileArgs $arguments The arguments used for compilation
     * @param RuleNode    $node      The rule node
     * @param string      $fieldName The field name with alias
     */
    public function compileFieldName(CompileArgs $arguments, RuleNode $node, string $fieldName): string;
}
