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
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;
use Klipper\Component\DoctrineExtensionsExtra\Util\DateTimeZoneUtil;

/**
 * UTC DateTime Type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UtcDateTimeType extends DateTimeType
{
    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTime) {
            $value = clone $value;
            $value->setTimezone(DateTimeZoneUtil::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * @param mixed $value
     *
     * @throws
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeInterface
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $converted = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            DateTimeZoneUtil::getUtc()
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        $converted->setTimezone(DateTimeZoneUtil::get(date_default_timezone_get()));

        return $converted;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
