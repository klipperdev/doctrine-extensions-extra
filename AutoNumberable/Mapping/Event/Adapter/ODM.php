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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Gedmo\Mapping\Event\Adapter\ODM as BaseAdapterODM;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfigInterface;
use Klipper\Component\DoctrineExtensionsExtra\Exception\RuntimeException;

/**
 * The auto numberable adapter for ODM.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ODM extends BaseAdapterODM implements AutoNumberableAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $type, string $pattern): AutoNumberConfigInterface
    {
        $om = $this->getObjectManager();
        $repo = $om->getRepository(AutoNumberConfigInterface::class);
        /** @var null|AutoNumberConfigInterface $config */
        $config = $repo->findOneBy([
            'type' => $type,
        ]);

        if (null === $config) {
            /** @var ClassMetadata $meta */
            $meta = $om->getClassMetadata(AutoNumberConfigInterface::class);
            $config = $meta->newInstance();
            $config->setType($type);
            $config->setPattern($pattern);
            $config->setNumber(0);
        }

        $config->setNumber($config->getNumber() + 1);
        $om->persist($om);

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function put(AutoNumberConfigInterface $config): void
    {
        /** @var DocumentManager $dm */
        $dm = $this->getObjectManager();
        /** @var ClassMetadata $meta */
        $meta = $dm->getClassMetadata(\get_class($config));
        $collection = $dm->getDocumentCollection($meta->name);
        $data = [];
        $identifier = [];

        /** @var \ReflectionProperty $reflProp */
        foreach ($meta->getReflectionProperties() as $fieldName => $reflProp) {
            $colName = $meta->fieldMappings[$fieldName]['name'];

            if ($meta->isIdentifier($fieldName)) {
                if (null !== ($idValue = $reflProp->getValue($config))) {
                    $identifier[$colName] = $idValue;
                }
            } else {
                $data[$colName] = $reflProp->getValue($config);
            }
        }

        if (!empty($identifier)) {
            $res = $collection->update($identifier, $data);
        } else {
            $res = $collection->insert($data);
        }

        if (!$res) {
            throw new RuntimeException('Failed to insert new auto number config record');
        }
    }
}
