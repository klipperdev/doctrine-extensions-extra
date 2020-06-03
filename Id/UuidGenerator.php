<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Id;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Ramsey\Uuid\Uuid;

/**
 * UUID v4 Generator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UuidGenerator extends AbstractIdGenerator
{
    /**
     * @param mixed $entity
     *
     * @throws
     */
    public function generate(EntityManager $em, $entity)
    {
        return Uuid::uuid4()->toString();
    }
}
