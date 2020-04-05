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
    /**
     * @var HtmlCleanerInterface
     */
    private $htmlCleaner;

    /**
     * Constructor.
     *
     * @param HtmlCleanerInterface $htmlCleaner The html cleaner
     */
    public function __construct(HtmlCleanerInterface $htmlCleaner)
    {
        parent::__construct();

        $this->htmlCleaner = $htmlCleaner;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigKey(): string
    {
        return 'htmlable';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUpdatedFieldValue(AdapterInterface $adapter, object $object, string $field, array $options, $oldValue, $newValue)
    {
        return $this->htmlCleaner->clean($newValue, $options['tags']);
    }
}
