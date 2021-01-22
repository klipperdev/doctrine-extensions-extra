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
 * Exception for invalid association.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InvalidAssociationException extends InvalidArgumentException
{
    public function __construct(string $association)
    {
        parent::__construct(sprintf('The association "%s" is not valid', $association));
    }
}
