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

use Gedmo\Mapping\Event\AdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\Listener\AbstractUpdateFieldSubscriber;

/**
 * The htmlable listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class HtmlableListener extends AbstractUpdateFieldSubscriber
{
    private HtmlCleanerInterface $htmlCleaner;

    /**
     * @param HtmlCleanerInterface $htmlCleaner The html cleaner
     */
    public function __construct(HtmlCleanerInterface $htmlCleaner)
    {
        parent::__construct();

        $this->htmlCleaner = $htmlCleaner;
    }

    protected function getConfigKey(): string
    {
        return 'htmlable';
    }

    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    protected function getUpdatedFieldValue(AdapterInterface $adapter, object $object, string $field, array $options, $oldValue, $newValue)
    {
        return $this->htmlCleaner->clean($newValue, $options['tags']);
    }
}
