<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Form\Type;

use Klipper\Component\DoctrineChoice\Model\ChoiceInterface;
use Klipper\Component\Form\Doctrine\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Doctrine Entity Choice form type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class EntityChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => ChoiceInterface::class,
            'query_builder' => static function (Options $options) {
                return $options['em']->getRepository($options['class'])->createQueryBuilder('c')
                    ->andWhere('c.type = :type')
                    ->setParameter('type', $options['type'])
                    ->addOrderBy('c.position', 'ASC')
                ;
            },
        ]);

        $resolver->setRequired([
            'type',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
