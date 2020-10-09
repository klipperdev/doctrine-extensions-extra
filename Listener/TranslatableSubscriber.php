<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Klipper\Component\DoctrineExtensionsExtra\Listener\Traits\CacheMetadatasTrait;
use Klipper\Component\DoctrineExtensionsExtra\Model\Traits\TranslatableInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableSubscriber implements EventSubscriber
{
    use CacheMetadatasTrait;

    protected string $fallback;

    /**
     * @param string $fallback The locale fallback
     */
    public function __construct(string $fallback)
    {
        $this->fallback = $fallback;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $event The event
     */
    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $object) {
            $this->buildAvailableLocales($uow, $object);
        }

        foreach ($uow->getScheduledEntityUpdates() as $object) {
            $this->buildAvailableLocales($uow, $object);
        }
    }

    /**
     * Build and validate the available locales of translatable object.
     *
     * @param UnitOfWork $uow    The unit of work
     * @param object     $object The object
     */
    private function buildAvailableLocales(UnitOfWork $uow, object $object): void
    {
        if (!$object instanceof TranslatableInterface) {
            return;
        }

        // force init of available locales
        $oldAvailables = $object->getAvailableLocales();
        $availables = $oldAvailables;
        $translationLocales = [];

        foreach ($object->getTranslations() as $translation) {
            $translationLocales[] = $translation->getLocale();
        }

        $translationLocales = array_unique($translationLocales);

        foreach ($translationLocales as $translationLocale) {
            if (!\in_array($translationLocale, $availables, true)) {
                $availables[] = $translationLocale;
            }
        }

        // add fallback locale if available locales is empty
        if (0 === \count($availables)) {
            $object->setTranslatableLocale($this->fallback);
            $availables[] = $this->fallback;
        }

        foreach ($availables as $available) {
            if ($this->fallback !== $available
                    && false === ($pos = array_search($available, $translationLocales, true))) {
                array_splice($availables, $pos, 0);
            }
        }

        $object->setAvailableLocales($availables);
    }
}
