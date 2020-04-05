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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Document\MappedSuperclass\AbstractPersonalTranslation as BaseDocumentPersonalTranslation;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation as BaseEntityPersonalTranslation;

/**
 * Trait of translatable.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait TranslatableTrait
{
    /**
     * @var Collection
     */
    protected $translations;

    /**
     * @var string
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @var array
     */
    protected $availableLocales;

    /**
     * {@inheritdoc}
     *
     * @return BaseDocumentPersonalTranslation[]|BaseEntityPersonalTranslation[]|Collection
     */
    public function getTranslations(): Collection
    {
        return $this->translations ?: $this->translations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslatableLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->setAvailableLocales(array_merge($this->getAvailableLocales(), [$locale]));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableLocales(array $availableLocales): self
    {
        asort($availableLocales);

        $this->availableLocales = array_unique($availableLocales);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales(): array
    {
        if (null === $this->availableLocales) {
            $this->availableLocales = [\Locale::getDefault()];
        }

        return $this->availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslationFields(string $locale): array
    {
        $availables = $this->getAvailableLocales();
        $removed = [];

        if (\count($availables) <= 1) {
            return $removed;
        }

        if (false !== ($pos = array_search($locale, $availables, true))) {
            array_splice($availables, $pos, 1);
            $this->setAvailableLocales($availables);
        }

        foreach ($this->getTranslations() as $translation) {
            if ($locale === $translation->getLocale()) {
                $this->getTranslations()->removeElement($translation);
                $removed[] = $translation;
            }
        }

        return $removed;
    }
}
