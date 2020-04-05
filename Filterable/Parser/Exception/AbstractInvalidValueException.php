<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception;

/**
 * Exception for invalid value.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractInvalidValueException extends InvalidArgumentException
{
    /**
     * @var string[]
     */
    protected $expectedValues;

    /**
     * @var string
     */
    protected $givenValue;

    /**
     * @var null|string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string[]    $expectedValues The expected rule types
     * @param string      $givenValue     The given type
     * @param null|string $path           The path of node
     */
    public function __construct(array $expectedValues, string $givenValue, ?string $path = null)
    {
        parent::__construct($this->buildMessage($expectedValues, $givenValue, $path));

        $this->expectedValues = $expectedValues;
        $this->givenValue = $givenValue;
        $this->path = $path;
    }

    /**
     * Get the expected values.
     *
     * @return string[]
     */
    public function getExpectedValues(): array
    {
        return $this->expectedValues;
    }

    /**
     * Get the given value.
     */
    public function getGivenValue(): string
    {
        return $this->givenValue;
    }

    /**
     * Get the path of node.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Build the exception message.
     *
     * @param string[]    $expectedValues The expected values
     * @param string      $givenValue     The given value
     * @param null|string $path           The path of node
     */
    abstract protected function buildMessage(array $expectedValues, string $givenValue, ?string $path = null): string;
}
