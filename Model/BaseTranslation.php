<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Model;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * The base of translation model.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class BaseTranslation extends AbstractPersonalTranslation
{
    /**
     * Constructor.
     *
     * @param string $locale The locale
     * @param string $field  The field name
     * @param string $value  The value of field
     */
    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
