<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception;

/**
 * Exception for invalid node type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidNodeTypeException extends InvalidArgumentException
{
    protected ?string $path;

    /**
     * @param null|string $path The path of node
     */
    public function __construct(?string $path = null)
    {
        parent::__construct(sprintf('The filter node "%s" must be an object', $path));

        $this->path = $path;
    }

    /**
     * Get the path of node.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
