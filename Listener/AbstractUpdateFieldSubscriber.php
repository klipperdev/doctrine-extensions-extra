<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo as OdmClassMetadataInfo;
use Doctrine\ODM\MongoDB\UnitOfWork as OdmUnitOfWork;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo as OrmClassMetadataInfo;
use Doctrine\ORM\UnitOfWork as OrmUnitOfWork;
use Gedmo\Mapping\Event\AdapterInterface;
use Gedmo\Mapping\MappedEventSubscriber;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractUpdateFieldSubscriber extends MappedEventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
            'onFlush',
        ];
    }

    /**
     * Maps additional metadata.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * Action on doctrine on flush.
     *
     * @param EventArgs $args The doctrine event
     */
    public function onFlush(EventArgs $args): void
    {
        $configKey = $this->getConfigKey();
        $ea = $this->getEventAdapter($args);
        /** @var DocumentManager|EntityManagerInterface $om */
        $om = $ea->getObjectManager();
        /** @var mixed|OdmUnitOfWork|OrmUnitOfWork $uow */
        $uow = $om->getUnitOfWork();
        $objects = array_merge($ea->getScheduledObjectInsertions($uow), $ea->getScheduledObjectUpdates($uow));

        foreach ($objects as $object) {
            $meta = $om->getClassMetadata(\get_class($object));

            /** @var array[] $config */
            if (!$config = $this->getConfiguration($om, $meta->name)) {
                continue;
            }

            $changeSet = $ea->getObjectChangeSet($uow, $object);

            foreach ($config[$configKey] as $options) {
                if (isset($changeSet[$options['field']])) {
                    $values = $changeSet[$options['field']];
                    $values[1] = $this->getUpdatedFieldValue($ea, $object, $options['field'], $options, $values[0], $values[1]);

                    /** @var OdmClassMetadataInfo|OrmClassMetadataInfo $meta */
                    $property = $meta->getReflectionProperty($options['field']);
                    $property->setValue($object, $values[1]);

                    $uow->propertyChanged($object, $options['field'], $values[0], $values[1]);
                }
            }
        }
    }

    /**
     * Get the key of the extension config.
     */
    abstract protected function getConfigKey(): string;

    /**
     * Get the new value.
     *
     * @param AdapterInterface $adapter  The event adapter
     * @param object           $object   The entity or document
     * @param string           $field    The field name
     * @param array            $options  The options of extension
     * @param null|mixed       $oldValue The old value
     * @param null|mixed       $newValue The new value
     *
     * @return mixed
     */
    abstract protected function getUpdatedFieldValue(AdapterInterface $adapter, object $object, string $field, array $options, $oldValue, $newValue);
}
