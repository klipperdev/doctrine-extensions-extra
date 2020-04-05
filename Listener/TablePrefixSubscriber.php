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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\Id\BigIntegerIdentityGenerator;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Id\TableGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TablePrefixSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * Constructor.
     */
    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata => 'loadClassMetadata',
        ];
    }

    /**
     * Load the class metadata.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event
     *
     * @throws
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->isRootEntity()) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix.$classMetadata->getTableName(),
            ]);
        }

        $this->replaceSequenceName($classMetadata->idGenerator);

        if (isset($classMetadata->sequenceGeneratorDefinition['sequenceName'])
                && 0 !== strpos($classMetadata->sequenceGeneratorDefinition['sequenceName'], $this->prefix)) {
            $classMetadata->sequenceGeneratorDefinition['sequenceName'] = $this->prefix.$classMetadata->sequenceGeneratorDefinition['sequenceName'];
        }

        foreach (['indexes', 'uniqueConstraints'] as $key) {
            if (isset($classMetadata->table[$key])) {
                $keys = $classMetadata->table[$key];
                foreach (array_keys($keys) as $oldKey) {
                    $keys = $this->renameKey($keys, $oldKey, $this->prefix);
                }
                $classMetadata->table[$key] = $keys;
            }
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (ClassMetadataInfo::MANY_TO_MANY === $mapping['type'] && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];

                if (0 !== strpos($mappedTableName, $this->prefix)) {
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix.$mappedTableName;
                }
            }
        }
    }

    /**
     * Rename the key in array.
     *
     * @param array  $data   The array
     * @param string $key    The key
     * @param string $prefix The prefix
     */
    public function renameKey(array $data, string $key, string $prefix): array
    {
        if (!\array_key_exists($key, $data) || 0 === strpos($key, $prefix)) {
            return $data;
        }

        $keys = array_keys($data);
        $keys[array_search($key, $keys, true)] = $prefix.$key;

        return array_combine($keys, $data);
    }

    /**
     * @throws \ReflectionException
     */
    private function replaceSequenceName(AbstractIdGenerator $generator): void
    {
        $ref = new \ReflectionClass($generator);
        $propName = null;

        if ($generator instanceof BigIntegerIdentityGenerator) {
            $propName = 'sequenceName';
        } elseif ($generator instanceof IdentityGenerator) {
            $propName = 'sequenceName';
        } elseif ($generator instanceof SequenceGenerator) {
            $propName = '_sequenceName';
        } elseif ($generator instanceof TableGenerator) {
            $propName = '_sequenceName';
        }

        if (!$propName) {
            return;
        }

        $prop = $ref->getProperty($propName);
        $prop->setAccessible(true);
        $name = $prop->getValue($generator);

        if (0 !== strpos($name, $this->prefix)) {
            $prop->setValue($generator, $this->prefix.$name);
        }
    }
}
