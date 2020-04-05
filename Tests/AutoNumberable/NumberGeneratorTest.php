<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests\AutoNumberable;

use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\NumberGenerator;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidAutoNumberPatternException;
use PHPUnit\Framework\TestCase;

/**
 * Number generator tests.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @group klipper
 * @group klipper-doctrine-extensions-extra
 *
 * @internal
 */
final class NumberGeneratorTest extends TestCase
{
    public function getPatterns(): array
    {
        $dt = new \DateTime('CET');

        return [
            ['{0}', '42'],
            ['{00}', '42'],
            ['{000}', '042'],
            ['{0000000000}', '0000000042'],
            ['{1234}-{0}', '{1234}-42'],
            ['{YYYY}-{0}', '2069-42'],
            ['{YY}-{0}', '69-42'],
            ['{MM}-{0}', '02-42'],
            ['{DD}-{0}', '18-42'],
            ['{hh}-{0}', '05-42'],
            ['{mm}-{0}', '02-42'],
            ['{ss}-{0}', '07-42'],
            ['{A}-{0}', 'AM-42'],
            ['{a}-{0}', 'am-42'],
            ['{o}-{0}', '/\+[\d]{4}-42/'],
            ['{tz}-{0}', 'CET-42'],
            ['{tz}-{0}', 'UTC-42', true],
            ['{YYYY}/{YY}-{MM}-{DD}[{hh}:{mm}:{ss}]@{A}\{a}{{o}}_{tz}# I{00000}', '2069/69-02-18[05:02:07]@AM\am{'.$dt->format('O').'}_CET# I00042'],
        ];
    }

    /**
     * @dataProvider getPatterns
     *
     * @param string $pattern
     * @param string $expected
     * @param bool   $utc
     *
     * @throws
     */
    public function testPattern($pattern, $expected, $utc = false): void
    {
        $datetime = new \DateTime('2069-02-18 05:02:07 CET');
        $generator = new NumberGenerator();
        $result = $generator->generate($pattern, 42, $datetime, $utc);

        if (0 === strpos($expected, '/')) {
            static::assertRegExp($expected, $result);
        } else {
            static::assertSame($expected, $result);
        }
    }

    public function getInvalidPatterns(): array
    {
        return [
            ['{YYYY}'],
            ['{YYYY}-{YY}'],
            ['{1}'],
            ['{1234}'],
        ];
    }

    /**
     * @dataProvider getInvalidPatterns
     *
     * @param string $pattern
     */
    public function testInvalidPattern($pattern): void
    {
        $this->expectException(InvalidAutoNumberPatternException::class);
        $this->expectExceptionMessage(sprintf('The auto number pattern "%s" is not valid', $pattern));

        $generator = new NumberGenerator();
        $generator->validate($pattern);
    }
}
