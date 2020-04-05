<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Metadata;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo as OrmClassMetadata;
use Gedmo\Mapping\MappedEventSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\Util\ListenerUtil;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Klipper\Component\Metadata\ActionMetadataBuilder;
use Klipper\Component\Metadata\AssociationMetadataBuilder;
use Klipper\Component\Metadata\FieldMetadataBuilder;
use Klipper\Component\Metadata\Loader\MetadataDynamicLoaderInterface;
use Klipper\Component\Metadata\Loader\ObjectMetadataNameCollection;
use Klipper\Component\Metadata\ObjectMetadataBuilder;
use Klipper\Component\Metadata\ObjectMetadataBuilderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MetadataListener extends MappedEventSubscriber implements MetadataDynamicLoaderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var PropertyAccessorInterface
     */
    protected $accessor;

    /**
     * Constructor.
     *
     * @param ManagerRegistry                $registry The doctrine registry
     * @param null|PropertyAccessorInterface $accessor The property accessor
     */
    public function __construct(ManagerRegistry $registry, ?PropertyAccessorInterface $accessor = null)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->accessor = $accessor ?: PropertyAccess::createPropertyAccessor();
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
     * {@inheritdoc}
     */
    public function loadNames(): ObjectMetadataNameCollection
    {
        $managers = $this->registry->getManagers();
        $names = new ObjectMetadataNameCollection();
        $resources = [];

        foreach ($managers as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                if (!$metadata instanceof OrmClassMetadata
                        || ($metadata instanceof OrmClassMetadata && !$metadata->isMappedSuperclass)) {
                    $class = $metadata->getName();
                    $config = $this->getConfiguration($manager, $class);

                    $names->add($class, $config['metadataObject']['name'] ?? null);
                    $resources = $this->findResources($metadata, $resources);
                } elseif ($metadata instanceof OrmClassMetadata && $metadata->isMappedSuperclass) {
                    $resources = $this->findResources($metadata, $resources);
                }
            }
        }

        foreach ($resources as $resource) {
            $names->addResource(new FileResource($resource));
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function loadBuilder(string $class): ?ObjectMetadataBuilderInterface
    {
        $manager = ManagerUtils::getManager($this->registry, $class);
        $builder = null;

        if (null !== $manager) {
            $metadata = $manager->getClassMetadata($class);
            $config = $this->getConfiguration($manager, $metadata->getName());
            $builder = $this->createBuilder($metadata, $config);
        }

        return $builder;
    }

    /**
     * On load class metadata.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $ea = $this->getEventAdapter($eventArgs);
        $metadata = $eventArgs->getClassMetadata();
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $metadata);
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * Create the object metadata builder.
     *
     * @param ClassMetadata $metadata The doctrine class metadata
     * @param array         $config   The doctrine metadata config
     */
    protected function createBuilder(ClassMetadata $metadata, array $config): ?ObjectMetadataBuilder
    {
        $builder = null;

        if (isset($config['metadataObject'])) {
            $builder = new ObjectMetadataBuilder($metadata->getName());
            ListenerUtil::mergeValues($this->accessor, $builder, $config['metadataObject']);

            if (isset($config['metadataDefaultAction'])) {
                $actionBuilder = new ActionMetadataBuilder($config['metadataDefaultAction']['name'] ?? '_default');
                ListenerUtil::mergeValues($this->accessor, $actionBuilder, $config['metadataDefaultAction']);
                $builder->setDefaultAction($actionBuilder);
            }

            if (isset($config['metadataActions'])) {
                foreach ($config['metadataActions'] as $action => $actionMetadataConfig) {
                    $actionBuilder = new ActionMetadataBuilder($action);
                    $builder->addAction($actionBuilder);
                    ListenerUtil::mergeValues($this->accessor, $actionBuilder, $actionMetadataConfig);
                }
            }

            if (isset($config['metadataFields'])) {
                foreach ($config['metadataFields'] as $field => $fieldMetadataConfig) {
                    $fieldBuilder = new FieldMetadataBuilder($field);
                    $builder->addField($fieldBuilder);
                    ListenerUtil::mergeValues($this->accessor, $fieldBuilder, $fieldMetadataConfig);
                }
            }

            if (isset($config['metadataAssociations'])) {
                foreach ($config['metadataAssociations'] as $association => $associationMetadataConfig) {
                    $associationBuilder = new AssociationMetadataBuilder($association);
                    $builder->addAssociation($associationBuilder);
                    ListenerUtil::mergeValues($this->accessor, $associationBuilder, $associationMetadataConfig);
                }
            }

            foreach ($this->findResources($metadata) as $resource) {
                $builder->addResource(new FileResource($resource));
            }
        }

        return $builder;
    }

    /**
     * Find the config resources of the class metadata.
     *
     * @param ClassMetadata $metadata  The class metadata
     * @param string[]      $resources The resource files
     *
     * @throws
     *
     * @return string[]
     */
    private function findResources(ClassMetadata $metadata, ?array $resources = null): array
    {
        $resources = $resources ?? [];
        $ref = $metadata->getReflectionClass();
        $file = realpath($ref->getFileName());

        if (\is_string($file) && !\in_array($file, $resources, true)) {
            $resources = $this->findResourceFile($resources, $ref);

            if ($metadata instanceof OrmClassMetadata) {
                $resources = $this->findResourceFiles($resources, $metadata->embeddedClasses);
                $resources = $this->findResourceFiles($resources, $metadata->parentClasses);
                $resources = $this->findResourceFiles($resources, $metadata->subClasses);
            }
        }

        return $resources;
    }

    /**
     * Find the resource files.
     *
     * @param string[]           $resources   The resource files
     * @param \ReflectionClass[] $reflections The reflection classes
     *
     * @return string[]
     */
    private function findResourceFiles(array $resources, array $reflections): array
    {
        foreach ($reflections as $ref) {
            $resources = $this->findResourceFile($resources, $ref);
        }

        return $resources;
    }

    /**
     * Find the resource files.
     *
     * @param string[]         $resources  The resource files
     * @param \ReflectionClass $reflection The reflection class
     *
     * @return string[]
     */
    private function findResourceFile(array $resources, \ReflectionClass $reflection): array
    {
        $refFile = realpath($reflection->getFileName());

        if (\is_string($refFile) && !\in_array($refFile, $resources, true)) {
            $resources[] = $refFile;

            foreach ($reflection->getInterfaces() as $interfaceRef) {
                $interfaceFile = realpath($interfaceRef->getFileName());

                if (\is_string($interfaceFile) && !\in_array($interfaceFile, $resources, true)) {
                    $resources[] = $interfaceFile;
                }
            }

            foreach ($reflection->getTraits() as $traitRef) {
                $traitFile = realpath($traitRef->getFileName());

                if (\is_string($traitFile) && !\in_array($traitFile, $resources, true)) {
                    $resources[] = $traitFile;
                }
            }
        }

        return $resources;
    }
}
