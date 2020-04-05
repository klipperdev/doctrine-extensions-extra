<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\ExpressionLanguage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Filterable field subscriber.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var null|ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * Constructor.
     *
     * @param null|ExpressionLanguage $expressionLanguage The expression language
     */
    public function __construct(ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    /**
     * Pre submit action.
     *
     * @param FormEvent $event The form event
     */
    public function preSubmit(FormEvent $event): void
    {
        $value = $event->getData();

        if ($this->expressionLanguage && $this->expressionLanguage->isEvaluable($value)) {
            try {
                $value = $this->expressionLanguage->evaluate($value);
            } catch (\Exception $e) {
                $event->getForm()->addError(new FormError($e->getMessage()));
            }
        }

        $event->setData($value);
    }
}
