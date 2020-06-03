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

use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Model\AutoNumberConfigInterface;
use Klipper\Component\DoctrineExtensionsExtra\Exception\RuntimeException;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * The auto numberable adapter for ORM.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class ORM extends BaseAdapterORM implements AutoNumberableAdapterInterface
{
    public function get(string $type, string $pattern): AutoNumberConfigInterface
    {
        $em = $this->getObjectManager();
        $repo = $em->getRepository(AutoNumberConfigInterface::class);
        /** @var null|AutoNumberConfigInterface $config */
        $config = $repo->findOneBy([
            'type' => $type,
        ]);

        if (null === $config) {
            $config = $em->getClassMetadata(AutoNumberConfigInterface::class)->newInstance();
            $config->setType($type);
            $config->setPattern($pattern);
            $config->setNumber(0);
        }

        return $config->setNumber($config->getNumber() + 1);
    }

    /**
     * @throws
     */
    public function put(AutoNumberConfigInterface $config): void
    {
        $em = $this->getObjectManager();
        $meta = $em->getClassMetadata(ClassUtils::getClass($config));
        $table = $meta->getTableName();
        $types = [];
        $data = [];
        $identifier = [];

        /** @var \ReflectionProperty $reflProp */
        foreach ($meta->getReflectionProperties() as $fieldName => $reflProp) {
            $colName = $meta->getColumnName($fieldName);
            $types[$colName] = $meta->getTypeOfField($fieldName);

            if ($meta->isIdentifier($fieldName)) {
                if (null !== ($idValue = $reflProp->getValue($config))) {
                    $identifier[$colName] = $idValue;
                }
            } else {
                $data[$colName] = $reflProp->getValue($config);
            }
        }

        if (!empty($identifier)) {
            $res = $em->getConnection()->update($table, $data, $identifier, $types);
        } else {
            $res = $em->getConnection()->insert($table, $data, $types);
        }

        if (!$res) {
            throw new RuntimeException('Failed to insert new auto number config record');
        }
    }
}
