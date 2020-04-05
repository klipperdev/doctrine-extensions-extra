<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event;

use Gedmo\Mapping\Event\AdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfigInterface;

/**
 * The auto numberable adapter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AutoNumberableAdapterInterface extends AdapterInterface
{
    /**
     * Get the auto number config for the type.
     *
     * @param string $type    The type
     * @param string $pattern The auto number pattern
     */
    public function get(string $type, string $pattern): AutoNumberConfigInterface;

    /**
     * Save the auto number config.
     *
     * @param AutoNumberConfigInterface $config The auto number config
     */
    public function put(AutoNumberConfigInterface $config): void;
}
