<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Form\Extension;

use Klipper\Component\DoctrineExtensionsExtra\Form\Type\TranslatableType;

/**
 * Translatable Form Extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableExtension extends AbstractTranslatableExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [TranslatableType::class];
    }
}
