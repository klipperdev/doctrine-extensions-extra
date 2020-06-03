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

use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\Exception\InvalidArgumentException;

/**
 * Auto number generator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AutoNumberGenerator implements AutoNumberGeneratorInterface
{
    protected NumberGeneratorInterface $numberGenerator;

    protected string $defaultPattern;

    protected ?AutoNumberableAdapterInterface $adapter = null;

    /**
     * @param NumberGeneratorInterface $numberGenerator The number generator
     * @param string                   $defaultPattern  The default pattern if any pattern is defined for a type
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        $defaultPattern = '{0}'
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->defaultPattern = $defaultPattern;
    }

    public function setEventAdapter(AutoNumberableAdapterInterface $adapter): void
    {
        $this->adapter = $adapter;
    }

    public function generate(string $type, ?string $defaultPattern = null, bool $utc = false): string
    {
        if (null === $this->adapter) {
            throw new InvalidArgumentException('The setEventAdapter() method must be called before the generate() method');
        }

        $defaultPattern = $defaultPattern ?? $this->defaultPattern;
        $config = $this->adapter->get($type, $defaultPattern);
        $number = $this->numberGenerator->generate($config->getPattern(), $config->getNumber(), null, $utc);
        $this->adapter->put($config);

        return $number;
    }
}
