<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filter\Listener;

use Klipper\Component\DoctrineExtensions\Filter\Listener\AbstractFilterSubscriber as BaseAbstractFilterSubscriber;
use Klipper\Component\Security\Event\SetCurrentOrganizationEvent;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractFilterSubscriber extends BaseAbstractFilterSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            SetCurrentOrganizationEvent::class => [
                ['onEvent', 0],
            ],
        ]);
    }
}
