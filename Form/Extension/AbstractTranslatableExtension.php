<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Form\Extension;

use Klipper\Component\DoctrineExtensionsExtra\Form\Util\FormUtil;
use Klipper\Component\DoctrineExtensionsExtra\Model\Traits\TranslatableInterface;
use Klipper\Component\HttpFoundation\Util\RequestUtil;
use Klipper\Contracts\Model\IdInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\ButtonBuilder;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Translatable Form Extension.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractTranslatableExtension extends AbstractTypeExtension
{
    protected ?Request $request;

    protected string $fallback = 'en';

    /**
     * @param RequestStack $requestStack The request stack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();
            $hasId = $this->hasId($data);
            $locale = RequestUtil::getRequestLanguage($this->request);
            $availables = [];

            if ($data instanceof TranslatableInterface) {
                if (!$hasId) {
                    $data->setAvailableLocales([RequestUtil::getLanguage($this->request)]);
                }

                $availables = $data->getAvailableLocales();
                $countAvailables = \count($availables);

                if (null !== $locale) {
                    $data->setTranslatableLocale($locale);
                } elseif ($countAvailables < 1
                    || (1 === $countAvailables && \in_array($this->fallback, $availables, true))) {
                    $data->setTranslatableLocale($this->fallback);
                }
            }

            if (!$data instanceof TranslatableInterface
                    || (null === $locale && !\in_array(\Locale::getDefault(), $data->getAvailableLocales(), true))) {
                return;
            }

            $isOtherLanguage = (!RequestUtil::isCurrentLanguage($this->request)
                || !\in_array(RequestUtil::getLanguage($this->request), $availables, true));

            foreach ($form->all() as $name => $child) {
                $childConfig = $child->getConfig();
                $disable = false;

                if ($hasId && $isOtherLanguage && !RequestUtil::isForcedLanguage($this->request)
                        && $childConfig->hasOption('translatable') && false === $childConfig->getOption('translatable')) {
                    $disable = true;
                }

                if ($disable) {
                    if (FormUtil::isFormType($child, CollectionType::class)) {
                        $this->disableFormCollection($child);
                    } else {
                        $this->disableForm($child);
                    }
                }
            }
        });
    }

    /**
     * Check if the object has an id.
     *
     * @param object $object The object instance
     */
    protected function hasId(object $object): bool
    {
        return \is_object($object) && (!$object instanceof IdInterface
            || ($object instanceof IdInterface && null !== $object->getId()));
    }

    /**
     * Disable the form collection.
     *
     * @param FormInterface $form The form
     */
    private function disableFormCollection(FormInterface $form): void
    {
        $this->disableForm($form);
        $proto = $form->getConfig()->getAttribute('prototype');

        if ($proto instanceof FormInterface) {
            $this->disableForm($proto);
            $this->disableAddonForm($proto, 'prepend');
            $this->disableAddonForm($proto, 'append');
        }
    }

    /**
     * Disable the addon form option.
     *
     * @param FormInterface $proto The form of prototype
     * @param string        $addon The name of addon option
     */
    private function disableAddonForm(FormInterface $proto, string $addon): void
    {
        if ($proto->getConfig()->hasOption($addon)) {
            $addonForm = $proto->getConfig()->getOption($addon);

            if ($addonForm instanceof FormInterface) {
                $this->disableForm($addonForm);
            }
        }
    }

    /**
     * Disable the form.
     *
     * @param FormInterface $form The form
     *
     * @throws
     */
    private function disableForm(FormInterface $form): void
    {
        $config = $form->getConfig();

        if ($config->getDisabled()) {
            return;
        }

        if ($config instanceof FormBuilder) {
            $ref = new \ReflectionClass($config);
            $pRef = $ref->getParentClass();
            $prop = $pRef->getProperty('disabled');
            $prop->setAccessible(true);
            $prop->setValue($config, true);
        } elseif ($config instanceof ButtonBuilder) {
            $ref = new \ReflectionClass($config);
            $prop = $ref->getProperty('disabled');
            $prop->setAccessible(true);
            $prop->setValue($config, true);
        }
    }
}
