<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Tests\Htmlable;

use Klipper\Component\DoctrineExtensionsExtra\Htmlable\HtmlCleaner;
use PHPUnit\Framework\TestCase;

/**
 * Html cleaner tests.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class HtmlCleanerTest extends TestCase
{
    public function getHtmlValues(): array
    {
        return [
            [
                'Test',
                '<p>Test</p>',
            ],
            [
                '<strong>Test</strong><p>Test 2</p>',
                '<strong>Test</strong><p>Test 2</p>',
            ],
            [
                '<strong>Test</strong><script type="text/javascript">console.log("test");</script><p>Test 2</p>',
                '<strong>Test</strong><p>Test 2</p>',
            ],
            [
                '<html><head><title>Title</title></head><body><strong>Test</strong><script type="text/javascript">console.log("test");</script><p>Test 2</p></body></html>',
                '<strong>Test</strong><p>Test 2</p>',
            ],
            [
                '<html><head><title>Title</title></head><body><strong>Test</strong><scripts type="text/javascript">console.log("test");</scripts><p>Test 2</p></body></html>',
                '<strong>Test</strong><scripts type="text/javascript">console.log("test");</scripts><p>Test 2</p>',
            ],
            [
                '<html><head></head><body></body></html>',
                null,
            ],
            [
                '<html><head></head><body>Test</body></html>',
                'Test',
            ],
            [
                'Test en UTF8 avec é, è, ç î, à, @, etc...',
                '<p>Test en UTF8 avec é, è, ç î, à, @, etc...</p>',
            ],
        ];
    }

    /**
     * @dataProvider getHtmlValues
     *
     * @param string $value
     * @param string $expected
     */
    public function testCleanValue(?string $value, ?string $expected): void
    {
        $cleaner = new HtmlCleaner(['script', 'link', 'title', 'meta', 'head']);

        static::assertSame($expected, $cleaner->clean($value));
    }
}
