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

use Klipper\Component\DoctrineExtensionsExtra\Form\ChoiceList\TranslatableQueryBuilderTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Translatable Entity Form Extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractTranslatableEntityExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'query_builder_transformer' => new TranslatableQueryBuilderTransformer(),
        ]);
    }
}
