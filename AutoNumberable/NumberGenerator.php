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
use Klipper\Component\DoctrineExtensionsExtra\Util\DateTimeZoneUtil;

/**
 * Number generator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class NumberGenerator implements NumberGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(string $pattern, int $value, ?\DateTime $datetime = null, bool $utc = false): string
    {
        $matches = $this->validateMatch($pattern);
        $datetime = null !== $datetime ? clone $datetime : new \DateTime();

        if ($utc) {
            $datetime->setTimezone(DateTimeZoneUtil::getUtc());
        }

        foreach ($matches[0] as $i => $match) {
            $matchValue = $matches[1][$i];

            if (is_numeric($matchValue)) {
                $matchValue = str_pad($value, \strlen($matchValue), '0', STR_PAD_LEFT);
            } else {
                $matchValue = $this->buildDateTime($matchValue, $datetime);
            }

            $pattern = str_replace($matches[0][$i], $matchValue, $pattern);
        }

        return $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(string $pattern): void
    {
        $this->validateMatch($pattern);
    }

    /**
     * Validate the pattern and return the matches.
     *
     * @param string $pattern The pattern
     */
    private function validateMatch(string $pattern): array
    {
        preg_match_all(NumberGeneratorInterface::PATTERN, $pattern, $matches);

        if (empty($matches) || !preg_match(NumberGeneratorInterface::PATTERN_VALIDATION, $pattern)) {
            throw new InvalidAutoNumberPatternException($pattern);
        }

        return $matches;
    }

    /**
     * Build the datetime value for pattern.
     *
     * @param string    $match    The regex match
     * @param \DateTime $dateTime The datetime
     */
    private function buildDateTime(string $match, \DateTime $dateTime): string
    {
        switch ($match) {
            case 'YYYY':
                $match = $dateTime->format('Y');

                break;
            case 'YY':
                $match = $dateTime->format('y');

                break;
            case 'MM':
                $match = $dateTime->format('m');

                break;
            case 'DD':
                $match = $dateTime->format('d');

                break;
            case 'hh':
                $match = $dateTime->format('h');

                break;
            case 'mm':
                $match = $dateTime->format('i');

                break;
            case 'ss':
                $match = $dateTime->format('s');

                break;
            case 'a':
                $match = $dateTime->format('a');

                break;
            case 'A':
                $match = $dateTime->format('A');

                break;
            case 'o':
                $match = $dateTime->format('O');

                break;
            case 'tz':
                $match = $dateTime->format('T');

                break;
            default:
                break;
        }

        return $match;
    }
}
