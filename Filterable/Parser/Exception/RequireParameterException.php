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

use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidArgumentException as BaseInvalidArgumentException;

/**
 * Exception for invalid argument.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequireParameterException extends BaseInvalidArgumentException implements ExceptionInterface
{
    protected string $parameter;

    protected ?string $path;

    /**
     * @param string      $parameter required parameter
     * @param null|string $path      The path of node
     */
    public function __construct(string $parameter, ?string $path = null)
    {
        parent::__construct(sprintf('The parameter "%s" of "%s" is required', $parameter, $path));

        $this->parameter = $parameter;
        $this->path = $path;
    }

    /**
     * Get the required parameter.
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }

    /**
     * Get the path of node.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
