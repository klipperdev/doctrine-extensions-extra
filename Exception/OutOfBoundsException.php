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
 * Exception for out of ounds.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}
