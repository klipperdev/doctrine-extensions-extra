<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Htmlable;

/**
 * The interface of HTML cleaner.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface HtmlCleanerInterface
{
    /**
     * @param null|string   $value   The value
     * @param null|string[] $tags    The html tags to be deleted
     * @param string        $charset The charset
     */
    public function clean(?string $value, ?array $tags = null, string $charset = 'UTF-8'): ?string;
}
