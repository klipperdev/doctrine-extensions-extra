<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\AutoNumberable;

use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidAutoNumberPatternException;

/**
 * Number generator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface NumberGeneratorInterface
{
    public const PATTERN = '/\{(YYYY|YY|MM|DD|hh|mm|ss|A|a|o|tz|[0]{1,})\}/';

    public const PATTERN_VALIDATION = '/\{([0]{1,})\}/';

    /**
     * Generate a new number defined by the pattern.
     *
     * @param string         $pattern  The pattern of number
     * @param int            $value    The value
     * @param null|\DateTime $datetime The datetime
     * @param bool           $utc      Check if the datetime must be converted in UTC
     *
     * @throws InvalidAutoNumberPatternException When the pattern is invalid
     */
    public function generate(string $pattern, int $value, ?\DateTime $datetime = null, bool $utc = false): string;

    /**
     * Validate the pattern.
     *
     * @param string $pattern The pattern of number
     *
     * @throws InvalidAutoNumberPatternException When the pattern is invalid
     *
     * @return static
     */
    public function validate(string $pattern);
}
