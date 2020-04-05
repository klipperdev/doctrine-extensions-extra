<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable;

use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;

/**
 * Auto number generator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface AutoNumberGeneratorInterface
{
    /**
     * Set the event adapter for auto numberable.
     *
     * @param AutoNumberableAdapterInterface $adapter The event adapter
     */
    public function setEventAdapter(AutoNumberableAdapterInterface $adapter): void;

    /**
     * Generate a new number defined by the pattern of type.
     *
     * @param string      $type           The auto number type
     * @param null|string $defaultPattern The pattern to use if the custom pattern isn't defined
     * @param bool        $utc            Check if the datetime must be converted in UTC
     */
    public function generate(string $type, ?string $defaultPattern = null, bool $utc = false): string;
}
