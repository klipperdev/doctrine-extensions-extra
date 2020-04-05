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
class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
{
}
