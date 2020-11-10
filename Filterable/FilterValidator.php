<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener\CollectibleSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener\FilterableFieldSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ConditionNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeError;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Parser;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\FieldMetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterValidator
{
    private MetadataManagerInterface $metadataManager;

    private Parser $parser;

    private FormFactoryInterface $formFactory;

    private TranslatorInterface $translator;

    private ?ExpressionLanguage $expressionLanguage;

    public function __construct(
        MetadataManagerInterface $metadataManager,
        Parser $parser,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ?ExpressionLanguage $expressionLanguage = null
    ) {
        $this->metadataManager = $metadataManager;
        $this->parser = $parser;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * @throw ObjectMetadataNotFoundException When the metadata is not found
     */
    public function validate(string $metadataName, array $filter, bool $forceFirstCondition = false): NodeInterface
    {
        $meta = $this->metadataManager->getByName($metadataName);

        $node = $this->parser->parse($filter, $forceFirstCondition);
        $this->validateNode($node, $meta);

        return $node;
    }

    private function validateNode(?NodeInterface $node, ObjectMetadataInterface $meta): void
    {
        if ($node instanceof ConditionNode) {
            $this->validateConditionNode($node, $meta);
        } elseif ($node instanceof RuleNode) {
            $this->validateRuleNode($node, $meta);
        }
    }

    private function validateConditionNode(ConditionNode $node, ObjectMetadataInterface $meta): void
    {
        foreach ($node->getRules() as $rule) {
            $this->validateNode($rule, $meta);
        }
    }

    private function validateRuleNode(RuleNode $node, ObjectMetadataInterface $meta): void
    {
        $metaForField = $meta;
        $field = $node->getField();

        if (false !== strpos($field, '.')) {
            $links = explode('.', $field);
            $field = array_pop($links);
            $metaForField = QueryUtil::getAssociationMeta(
                $this->metadataManager,
                $meta,
                $links
            );
        }

        $fieldMeta = $metaForField && $metaForField->hasFieldByName($field)
            ? $metaForField->getFieldByName($field)
            : null;

        if (null === $fieldMeta) {
            $msgParams = [
                '{{ field }}' => $node->getField(),
            ];
            $msg = $this->translator->trans('doctrine_filterable.invalid_field', $msgParams, 'validators');
            $node->addError(new NodeError($msg, $msg, $msgParams));
        } elseif ($fieldMeta && $fieldMeta->isFilterable()) {
            $this->validateRuleNodeOperator($node, $fieldMeta);
            $this->validateRuleNodeValue($node, $fieldMeta);
        }
    }

    private function validateRuleNodeOperator(RuleNode $node, FieldMetadataInterface $fieldMeta): void
    {
        $maps = $this->parser->getMapRules();
        $operator = $node->getOperator();
        $type = $fieldMeta->getType();

        if (!isset($maps[$type]) || !\in_array($operator, $maps[$type], true)) {
            $msgParams = [
                '{{ field }}' => $node->getField(),
                '{{ operator }}' => $operator,
            ];
            $msg = $this->translator->trans('doctrine_filterable.invalid_field_operator', $msgParams, 'validators');
            $node->addError(new NodeError($msg, $msg, $msgParams));
        }
    }

    /**
     * Validate the value of rule node.
     *
     * @param RuleNode               $node      The rule node
     * @param FieldMetadataInterface $fieldMeta The field metadata
     */
    private function validateRuleNodeValue(RuleNode $node, FieldMetadataInterface $fieldMeta): void
    {
        $form = $this->buildFormForNodeValue($node, $fieldMeta);
        $form->submit($node->getValue());

        if (!$form->isValid()) {
            /** @var FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $node->addError(new NodeError(
                    $error->getMessage(),
                    $error->getMessageTemplate(),
                    $error->getMessageParameters(),
                    $error->getMessagePluralization(),
                    $error->getCause()
                ));
            }
        } else {
            $node->setQueryValue($form->getData());
        }
    }

    /**
     * Build the form for the node value.
     *
     * @param RuleNode               $node      The rule node
     * @param FieldMetadataInterface $fieldMeta The field metadata
     */
    private function buildFormForNodeValue(RuleNode $node, FieldMetadataInterface $fieldMeta): FormInterface
    {
        $maps = $this->parser->getMapForms();
        $type = $this->getFieldType($fieldMeta);

        if (isset($maps[$type])) {
            $formType = (array) $maps[$type];
            $options = isset($formType[1]) && \is_array($formType[1]) ? $formType[1] : [];
            $options['csrf_protection'] = false;

            if ($node->isRequiredValue()) {
                $options['constraints'][] = new NotNull();
            }

            if ($node->isCollectible()) {
                $lockedCollection = $node->getSizeCollection() > 0;
                $data = $lockedCollection ? $this->buildLockedCollection($node) : null;

                $formBuilder = $this->formFactory->createBuilder(CollectionType::class, $data, [
                    'entry_type' => $formType[0],
                    'entry_options' => $options,
                    'allow_add' => $lockedCollection ? false : true,
                    'allow_delete' => $lockedCollection ? false : true,
                    'prototype' => false,
                    'csrf_protection' => false,
                    'constraints' => [new Valid()],
                ]);

                if (!$lockedCollection) {
                    $formBuilder->addEventSubscriber(new CollectibleSubscriber());
                }
            } else {
                $formBuilder = $this->formFactory->createBuilder($formType[0], null, $options);
            }
        } else {
            $formBuilder = $this->formFactory->createBuilder(TextType::class, null, ['csrf_protection' => false]);
        }

        $formBuilder->addEventSubscriber(new FilterableFieldSubscriber($this->expressionLanguage));

        return $formBuilder->getForm();
    }

    private function buildLockedCollection(RuleNode $node): array
    {
        $data = [];

        for ($i = 0; $i < $node->getSizeCollection(); ++$i) {
            $data[] = null;
        }

        return $data;
    }

    private function getFieldType(FieldMetadataInterface $fieldMeta): string
    {
        $maps = $this->parser->getMapForms();
        $type = $fieldMeta->getType();

        if ('integer' === $type
                && $fieldMeta->getParent()->getFieldIdentifier() === $fieldMeta->getField()
                && isset($maps['string'])) {
            $type = 'string';
        }

        return $type;
    }
}
