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

use Gedmo\Mapping\Event\AdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\AutoNumberable\Mapping\Event\AutoNumberableAdapterInterface;
use Klipper\Component\DoctrineExtensionsExtra\Listener\AbstractUpdateFieldSubscriber;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;

/**
 * The auto numberable listener.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class AutoNumberableListener extends AbstractUpdateFieldSubscriber
{
    protected AutoNumberGeneratorInterface $autoNumberGenerator;

    protected ExpressionLanguage $expressionLanguage;

    /**
     * @param AutoNumberGeneratorInterface $autoNumberGenerator The auto number generator
     * @param ExpressionLanguage           $expressionLanguage  The expression language
     */
    public function __construct(
        AutoNumberGeneratorInterface $autoNumberGenerator,
        ExpressionLanguage $expressionLanguage
    ) {
        parent::__construct();

        $this->autoNumberGenerator = $autoNumberGenerator;
        $this->expressionLanguage = $expressionLanguage;
    }

    protected function getConfigKey(): string
    {
        return 'autoNumberable';
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
        $expValues = $this->getExpressionValues($object, $field, $oldValue, $newValue);

        if ($adapter instanceof AutoNumberableAdapterInterface) {
            $this->autoNumberGenerator->setEventAdapter($adapter);
        }

        if ((empty($options['condition']) && null === $newValue)
                || $this->expressionLanguage->evaluate($options['condition'], $expValues)) {
            $newValue = $this->autoNumberGenerator->generate(
                $type = ClassUtils::getClass($object).'::'.$field,
                $options['pattern'],
                $options['utc']
            );
        }

        return $newValue;
    }

    /**
     * Get the values for the expression language condition.
     *
     * @param object     $object   The entity or document
     * @param string     $field    The field name
     * @param null|mixed $oldValue The old value
     * @param null|mixed $newValue The new value
     */
    protected function getExpressionValues(object $object, string $field, $oldValue, $newValue): array
    {
        return [
            'object' => $object,
            'field' => $field,
            'oldValue' => $oldValue,
            'newValue' => $newValue,
            'value' => $newValue,
        ];
    }
}
