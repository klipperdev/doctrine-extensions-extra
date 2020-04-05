<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Htmlable\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ODM as BaseAdapterODM;
use Klipper\Component\DoctrineExtensionsExtra\Htmlable\Mapping\Event\HtmlableAdapter;

/**
 * The htmlable adapter for ODM.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ODM extends BaseAdapterODM implements HtmlableAdapter
{
}
