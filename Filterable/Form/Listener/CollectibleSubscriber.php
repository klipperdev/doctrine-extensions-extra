<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Collectible field subscriber.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class CollectibleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => ['preSubmit', 100],
        ];
    }

    /**
     * Pre submit action.
     *
     * @param FormEvent $event The form event
     */
    public function preSubmit(FormEvent $event): void
    {
        $value = $event->getData();

        if (\is_string($value)) {
            $value = array_map('trim', explode(';', trim($value, ';')));
        }

        $event->setData($value);
    }
}
