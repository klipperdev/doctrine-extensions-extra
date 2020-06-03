<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateType;

/**
 * UTC Date Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UtcDateType extends DateType
{
    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value->setTime(0, 0);
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $converted = parent::convertToPHPValue($value, $platform);

        if ($converted instanceof \DateTime) {
            $converted->setTime(0, 0);
        }

        return $converted;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
