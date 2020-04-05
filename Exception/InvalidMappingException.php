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

use Gedmo\Exception\InvalidMappingException as BaseInvalidMappingException;

/**
 * Exception for invalid mapping.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidMappingException extends BaseInvalidMappingException implements ExceptionInterface
{
}
