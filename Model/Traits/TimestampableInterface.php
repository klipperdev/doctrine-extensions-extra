<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Model\Traits;

use Gedmo\Timestampable\Timestampable;

/**
 * Timestampable interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TimestampableInterface extends Timestampable, \Klipper\Contracts\Model\TimestampableInterface
{
}
