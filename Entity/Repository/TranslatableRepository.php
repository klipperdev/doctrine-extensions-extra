<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\TranslatableRepositoryInterface;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\TranslatableRepositoryTrait;

/**
 * Translatable entity repository class.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableRepository extends EntityRepository implements TranslatableRepositoryInterface
{
    use TranslatableRepositoryTrait;
}
