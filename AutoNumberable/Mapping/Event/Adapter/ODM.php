<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ODM as BaseAdapterODM;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfigInterface;
use Klipper\Component\DoctrineExtensionsExtra\Exception\RuntimeException;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * The auto numberable adapter for ODM.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ODM extends BaseAdapterODM implements AutoNumberableAdapterInterface
{
    public function get(string $type, string $pattern): AutoNumberConfigInterface
    {
        $om = $this->getObjectManager();
        $repo = $om->getRepository(AutoNumberConfigInterface::class);

        /** @var null|AutoNumberConfigInterface $config */
        $config = $repo->findOneBy([
            'type' => $type,
        ]);

        if (null === $config) {
            $config = $om->getClassMetadata(AutoNumberConfigInterface::class)->newInstance();
            $config->setType($type);
            $config->setPattern($pattern);
            $config->setNumber(0);
        }

        return $config->setNumber($config->getNumber() + 1);
    }

    public function put(AutoNumberConfigInterface $config): void
    {
        $em = $this->getObjectManager();
        $uow = $em->getUnitOfWork();

        try {
            $meta = $em->getMetadataFactory()->getMetadataFor(ClassUtils::getClass($config));
            $uow->persist($config);
            $uow->computeChangeSet($meta, $config);
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to insert new auto number config record', 0, $e);
        }
    }
}
