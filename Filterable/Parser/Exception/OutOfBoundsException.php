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

use Klipper\Component\DoctrineExtensionsExtra\Exception\OutOfBoundsException as BaseOutOfBoundsException;

/**
 * Exception for out of bounds.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OutOfBoundsException extends BaseOutOfBoundsException implements ExceptionInterface
{
}
