<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Klipper\Component\DoctrineExtensionsExtra\DBAL\Types\UtcDateType;
use Klipper\Component\DoctrineExtensionsExtra\Util\DateTimeZoneUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * UTC Date Type tests.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @group klipper
 * @group klipper-doctrine-extensions-extra
 *
 * @internal
 */
final class UtcDateTypeTest extends TestCase
{
    /**
     * @var AbstractPlatform|MockObject
     */
    protected $platform;

    protected ?string $defaultTimezone = null;

    protected ?UtcDateType $type = null;

    /**
     * @throws
     */
    protected function setUp(): void
    {
        $this->platform = $this->getMockForAbstractClass(AbstractPlatform::class);
        $this->defaultTimezone = date_default_timezone_get();

        Type::overrideType('date', UtcDateType::class);

        /** @var UtcDateType $type */
        $type = Type::getType('date');
        $this->type = $type;
        static::assertInstanceOf(UtcDateType::class, $this->type);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);

        $this->platform = null;
        $this->defaultTimezone = null;
        $this->type = null;
    }

    public function getTimezones(): array
    {
        return [
            ['Pacific/Tahiti'],
            ['Europe/Paris'],
            ['Pacific/Apia'],
            ['UTC'],
            ['Etc/GMT-12'],
            ['Etc/GMT+12'],
        ];
    }

    /**
     * @dataProvider getTimezones
     */
    public function testConvertToDatabaseValue(string $timezone): void
    {
        date_default_timezone_set($timezone);

        $date = $this->getDate($timezone, '01:00:00');
        $val = $this->type->convertToDatabaseValue($date, $this->platform);

        static::assertSame($date->format('Y-m-d'), $val);
        static::assertSame('00:00:00', $date->format('H:i:s'));
    }

    /**
     * @dataProvider getTimezones
     */
    public function testConvertToPHPValue(string $timezone): void
    {
        $format = 'Y-m-d H:i:s T Z';
        $dateDb = $this->getDate($timezone);
        $dateDbString = $dateDb->format('Y-m-d');

        date_default_timezone_set($timezone);

        $val = $this->type->convertToPHPValue($dateDbString, $this->platform);
        static::assertSame($dateDb->format($format), $val->format($format));
    }

    /**
     * @throws
     */
    protected function getDate(string $timezone, string $time = '00:00:00'): \DateTime
    {
        $now = new \DateTime();

        $date = new \DateTime($now->format('Y-m-d').' '.$time, DateTimeZoneUtil::get($timezone));
        static::assertSame($timezone, $date->getTimezone()->getName());

        return $date;
    }
}
