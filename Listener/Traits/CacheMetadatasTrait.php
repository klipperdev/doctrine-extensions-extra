<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Listener\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait CacheMetadatasTrait
{
    /**
     * @var array|ClassMetadataInfo[]
     */
    protected $cacheMetas = [];

    /**
     * Get the doctrine class metadata of entity.
     *
     * @param EntityManagerInterface $em     The entity manager
     * @param object                 $entity The entity
     */
    protected function getMeta(EntityManagerInterface $em, object $entity): ClassMetadataInfo
    {
        $class = ClassUtils::getClass($entity);

        if (!isset($this->cacheMetas[$class])) {
            $this->cacheMetas[$class] = $em->getClassMetadata($class);
        }

        return $this->cacheMetas[$class];
    }
}
