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
 * Exception for invalid rule node type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidRuleTypeException extends AbstractInvalidValueException
{
    /**
     * {@inheritdoc}
     */
    protected function buildMessage(array $expectedValues, string $givenValue, ?string $path = null): string
    {
        return sprintf('The operator of filter rule "%s" can only be "%s". Given "%s"', $path, implode('", "', array_keys($expectedValues)), $givenValue);
    }
}
