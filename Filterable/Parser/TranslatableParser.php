<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\ExceptionInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidConditionTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidNodeTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidRuleTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\RequireParameterException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\UnexpectedTypeException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Parser of filters with translated exception.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TranslatableParser extends Parser
{
    protected ?TranslatorInterface $translator = null;

    /**
     * Set the translator.
     *
     * @param TranslatorInterface $translator The translator
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function parse(string $json, bool $forceFirstCondition = true): NodeInterface
    {
        try {
            return parent::parse($json, $forceFirstCondition);
        } catch (ExceptionInterface $e) {
            if ($e instanceof InvalidConditionTypeException) {
                $mess = 'invalid_condition_type';
                $params = [
                    '{{ path }}' => $e->getPath(),
                    '{{ expected_values }}' => implode('", "', array_keys($e->getExpectedValues())),
                    '{{ value }}' => $e->getGivenValue(),
                ];
            } elseif ($e instanceof InvalidRuleTypeException) {
                $mess = 'invalid_rule_type';
                $params = [
                    '{{ path }}' => $e->getPath(),
                    '{{ expected_values }}' => implode('", "', array_keys($e->getExpectedValues())),
                    '{{ value }}' => $e->getGivenValue(),
                ];
            } elseif ($e instanceof InvalidNodeTypeException) {
                $mess = 'invalid_node_type';
                $params = [
                    '{{ path }}' => $e->getPath(),
                ];
            } elseif ($e instanceof RequireParameterException) {
                $mess = 'require_parameter';
                $params = [
                    '{{ parameter }}' => $e->getParameter(),
                    '{{ path }}' => $e->getPath(),
                ];
            } elseif ($e instanceof UnexpectedTypeException) {
                $mess = 'unexpected_type';
                $params = [
                    '{{ path }}' => $e->getPath(),
                    '{{ expected_values }}' => implode('", "', array_keys($e->getExpectedValues())),
                    '{{ value }}' => $e->getGivenValue(),
                ];
            } else {
                $mess = 'invalid_format';
                $params = [];
            }

            throw new BadRequestHttpException($this->translator->trans('doctrine_filterable.parser.'.$mess, $params, 'validators'));
        }
    }
}
