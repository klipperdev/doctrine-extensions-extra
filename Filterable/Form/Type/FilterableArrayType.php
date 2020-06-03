<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Type;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers\FindInSetTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filterable array form type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new CallbackTransformer(
                static function ($value) {
                    return $value;
                },
                static function ($value) {
                    if (\is_string($value)) {
                        $value = new FindInSetTransformer($value);
                    }

                    return $value;
                }
            )
        );
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
