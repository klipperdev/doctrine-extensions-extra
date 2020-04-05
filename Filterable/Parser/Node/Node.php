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

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeError;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeErrorIterator;

/**
 * Node of filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class Node implements NodeInterface
{
    /**
     * @var NodeError[]
     */
    private $errors = [];

    /**
     * {@inheritdoc}
     */
    public function addError(NodeError $error): self
    {
        if (null === $error->getOrigin()) {
            $error->setOrigin($this);
        }

        $this->errors[] = $error;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(bool $deep = false, bool $flatten = true): NodeErrorIterator
    {
        $errors = $this->errors;

        if ($deep && $this instanceof ConditionNode) {
            /** @var Node $child */
            foreach ($this->getRules() as $child) {
                if ($child->isValid()) {
                    continue;
                }

                $iterator = $child->getErrors(true, $flatten);

                if (0 === \count($iterator)) {
                    continue;
                }

                if ($flatten) {
                    foreach ($iterator as $error) {
                        $errors[] = $error;
                    }
                } else {
                    $errors[] = $iterator;
                }
            }
        }

        return new NodeErrorIterator($this, $errors);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return 0 === \count($this->getErrors(true));
    }
}
