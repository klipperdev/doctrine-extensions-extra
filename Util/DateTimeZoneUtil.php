<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Util;

/**
 * Date Time Zone Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class DateTimeZoneUtil
{
    /**
     * @var \DateTimeZone[]
     */
    private static array $timezones = [];

    /**
     * Get the UTC date time zone.
     */
    public static function getUtc(): \DateTimeZone
    {
        return self::get('UTC');
    }

    /**
     * Get the date time zone of timezone.
     *
     * @param string $timezone The name of timezone
     */
    public static function get(string $timezone): \DateTimeZone
    {
        return self::$timezones[$timezone] ?? self::$timezones[$timezone] = new \DateTimeZone($timezone);
    }
}
