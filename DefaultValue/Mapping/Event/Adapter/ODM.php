<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ODM as BaseAdapterODM;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Mapping\Event\DefaultValueAdapterInterface;

/**
 * The default value adapter for ODM.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ODM extends BaseAdapterODM implements DefaultValueAdapterInterface
{
}
