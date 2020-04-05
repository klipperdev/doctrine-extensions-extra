<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeError;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeErrorIterator;

/**
 * Interface of node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NodeInterface
{
    /**
     * Get the name of node.
     */
    public function getName(): string;

    /**
     * Add error.
     *
     * @param NodeError $error The node error
     *
     * @return static
     */
    public function addError(NodeError $error);

    /**
     * Returns the errors of this node.
     *
     * @param bool $deep    Whether to include errors of child nodes as well
     * @param bool $flatten Whether to flatten the list of errors in case
     *                      $deep is set to true
     *
     * @return NodeErrorIterator An iterator over the {@link NodeError}
     *                           instances that where added to this node
     */
    public function getErrors(bool $deep = false, bool $flatten = true): NodeErrorIterator;

    /**
     * Returns whether the node and all children are valid.
     */
    public function isValid(): bool;

    /**
     * Compile the node in Doctrine Query Language.
     *
     * @param CompileArgs $arguments The arguments used for compilation
     */
    public function compile(CompileArgs $arguments): string;
}
