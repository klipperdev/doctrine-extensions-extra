<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DefaultValue;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo as OrmClassMetadata;
use Gedmo\Mapping\MappedEventSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Loader\LoaderInterface;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Metadata\ClassMetadata;
use Klipper\Component\DoctrineExtensionsExtra\DefaultValue\Metadata\FieldMetadata;

/**
 * The default value listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DefaultValueListener extends MappedEventSubscriber implements LoaderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The doctrine registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * On load class metadata.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * {@inheritdoc}
     */
    public function load(): array
    {
        $managers = $this->registry->getManagers();
        $metadatas = [];

        foreach ($managers as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                if (!$metadata instanceof OrmClassMetadata
                    || ($metadata instanceof OrmClassMetadata && !$metadata->isMappedSuperclass)) {
                    $class = $metadata->getName();
                    $config = $this->getConfiguration($manager, $class);

                    if (isset($config['defaultValue'])) {
                        $fields = [];
                        foreach ($config['defaultValue'] as $fieldMeta) {
                            $fields[] = new FieldMetadata($fieldMeta['field'], $fieldMeta['expression']);
                        }

                        $metadatas[$class] = new ClassMetadata($class, $fields);
                    }
                }
            }
        }

        return $metadatas;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }
}
