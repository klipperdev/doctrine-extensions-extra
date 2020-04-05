<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Form\Util;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Form\Util\FormUtil as BaseFormUtil;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FormUtil extends BaseFormUtil
{
    /**
     * Check if form is a specific type.
     *
     * @param FormInterface   $form  The form
     * @param string|string[] $types The class name of types
     */
    public static function isFormType(FormInterface $form, $types): bool
    {
        return static::isType((array) $types, $form->getConfig()->getType());
    }

    /**
     * Check if the parent type of the current type is allowed.
     *
     * @param string[]                  $types The class name of types
     * @param ResolvedFormTypeInterface $rType The resolved form type
     */
    protected static function isType(array $types, ?ResolvedFormTypeInterface $rType = null): bool
    {
        if (null === $rType) {
            return false;
        }

        if (!\in_array(\get_class($rType->getInnerType()), $types, true)) {
            return static::isType($types, $rType->getParent());
        }

        return true;
    }
}
