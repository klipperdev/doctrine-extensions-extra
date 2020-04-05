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

use Doctrine\Common\Collections\Collection;
use Gedmo\Translatable\Document\MappedSuperclass\AbstractPersonalTranslation as BaseDocumentPersonalTranslation;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation as BaseEntityPersonalTranslation;
use Gedmo\Translatable\Translatable;

/**
 * Interface to manage personal translation.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TranslatableInterface extends Translatable, \Klipper\Contracts\Model\TranslatableInterface
{
    /**
     * Get the translations of model.
     *
     * @return BaseDocumentPersonalTranslation[]|BaseEntityPersonalTranslation[]|Collection
     */
    public function getTranslations(): Collection;

    /**
     * Remove all translated fields for a specific locale.
     * Return the removed translated fields.
     *
     * @return BaseDocumentPersonalTranslation[]|BaseEntityPersonalTranslation[]
     */
    public function removeTranslationFields(string $locale): array;
}
