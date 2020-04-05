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

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers\JsonGetTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filterable object form type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new CallbackTransformer(
                static function ($value) {
                    return $value;
                },
                static function ($value) {
                    if (\is_array($value) && isset($value['key'], $value['value'])) {
                        $value = new JsonGetTransformer($value['key'], $value['value']);
                    } else {
                        throw new TransformationFailedException('The filter value must be an object with the key and value parameters');
                    }

                    return $value;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
