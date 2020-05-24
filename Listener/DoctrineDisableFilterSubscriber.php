<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineDisableFilterSubscriber implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $parameter;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry  The doctrine entity manager
     * @param string          $parameter The parameter name in request
     */
    public function __construct(ManagerRegistry $registry, string $parameter = '_disable_filters')
    {
        $this->registry = $registry;
        $this->parameter = $parameter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0],
            ],
        ];
    }

    /**
     * Disable the doctrine filter of organization.
     *
     * @param RequestEvent $event The event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $attr = $event->getRequest()->attributes;
        $keys = $event->getRequest()->attributes->keys();
        $disableFilters = \in_array($this->parameter, $keys, true)
            ? $this->getFilterNames($attr, $this->parameter)
            : [];

        // add sql filter names without override the previous sql filter names
        if (\count($nextKeys = preg_grep('/^'.preg_quote($this->parameter.'__').'/', $keys)) > 0) {
            foreach ($nextKeys as $key) {
                if ($key === $this->parameter || 0 === strpos($key, $this->parameter.'__')) {
                    $disableFilters = array_merge(
                        $disableFilters,
                        $this->getFilterNames($attr, $key)
                    );
                }
            }
        }

        foreach ($this->registry->getManagers() as $om) {
            if ($om instanceof EntityManagerInterface) {
                SqlFilterUtil::disableFilters($om, array_unique($disableFilters));
            }
        }
    }

    /**
     * Get the filter names.
     *
     * @param ParameterBag $attr The parameter bag attributes of request
     * @param string       $key  The attribute key of filter names
     *
     * @return string[]
     */
    private function getFilterNames(ParameterBag $attr, string $key): array
    {
        return array_map('trim', explode(',', $attr->get($key)));
    }
}
