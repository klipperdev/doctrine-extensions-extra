<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Exception;

/**
 * Exception for invalid auto number pattern.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidAutoNumberPatternException extends InvalidArgumentException
{
    protected string $pattern;

    public function __construct(string $pattern, int $code = 0, ?\Throwable $previous = null)
    {
        $this->pattern = $pattern;
        $message = sprintf('The auto number pattern "%s" is not valid', $pattern);

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the invalid auto number pattern.
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
}
