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

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Entity Criteria Form Extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractEntityCriteriaExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'criteria' => null,
            'query_builder' => static function (Options $options, $value) {
                if (null === $options['criteria']) {
                    return $value;
                }

                return static function (ObjectRepository $or) use ($options) {
                    /** @var QueryBuilder $qb */
                    $qb = $or->createQueryBuilder('o');

                    foreach ($options['criteria'] as $field => $value) {
                        $valField = 'val_'.$field;
                        $qb->andWhere('o.'.$field.' = :'.$valField);
                        $qb->setParameter($valField, $value);
                    }

                    return $qb;
                };
            },
        ]);

        $resolver->addAllowedTypes('criteria', ['null', 'array']);
    }
}
