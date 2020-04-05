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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener\CollectibleSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Form\Listener\FilterableFieldSubscriber;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ConditionNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeError;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Parser;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\JoinsWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\MergeConditionalExpressionWalker;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\FieldMetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestFilterableQuery
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var MetadataManagerInterface
     */
    protected $metadataManager;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var null|ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var null|AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $queryFields = [];

    /**
     * Constructor.
     *
     * @param RequestStack                       $requestStack       The request stack
     * @param MetadataManagerInterface           $metadataManager    The metadata manager
     * @param Parser                             $parser             The parser of filterable
     * @param FormFactoryInterface               $formFactory        The form factory
     * @param TranslatorInterface                $translator         The translator
     * @param null|ExpressionLanguage            $expressionLanguage The expression language
     * @param null|AuthorizationCheckerInterface $authChecker        The authorization checker
     */
    public function __construct(
        RequestStack $requestStack,
        MetadataManagerInterface $metadataManager,
        Parser $parser,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ?ExpressionLanguage $expressionLanguage = null,
        ?AuthorizationCheckerInterface $authChecker = null
    ) {
        $this->requestStack = $requestStack;
        $this->metadataManager = $metadataManager;
        $this->parser = $parser;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->expressionLanguage = $expressionLanguage;
        $this->authChecker = $authChecker;
    }

    /**
     * Filter the query.
     *
     * @param Query $query The query
     */
    public function filter(Query $query): void
    {
        /** @var Query\AST\IdentificationVariableDeclaration[] $varDeclarations */
        $varDeclarations = $query->getAST()->fromClause->identificationVariableDeclarations;
        $class = null;

        foreach ($varDeclarations as $varDeclaration) {
            $rangeDeclaration = $varDeclaration->rangeVariableDeclaration;
            $class = $rangeDeclaration->abstractSchemaName;

            if ($rangeDeclaration->isRoot && $this->metadataManager->has($class)) {
                $this->doFilter($query, $class, $rangeDeclaration->aliasIdentificationVariable);

                break;
            }
        }
    }

    /**
     * Validate the filter for a specific metadata.
     *
     * @param string $metadataName The name of object metadata
     * @param string $filter       The filter in json
     *
     * @return false|NodeInterface
     */
    public function validate(string $metadataName, string $filter)
    {
        $node = null;

        if ($this->metadataManager->hasByName($metadataName)) {
            $meta = $this->metadataManager->getByName($metadataName);
            $alias = $this->getNameAlias($meta);
            $node = $this->validateNode($this->parser->parse($filter, false), $meta, $alias);
        }

        return $node instanceof NodeInterface ? $node : false;
    }

    /**
     * Sort the query.
     *
     * @param Query  $query The query
     * @param string $class The root class name
     * @param string $alias The alias
     */
    protected function doFilter(Query $query, $class, $alias): void
    {
        $queryFilter = $this->getQueryFilter();

        if (empty($queryFilter)) {
            return;
        }

        QueryUtil::addCustomTreeWalker($query, JoinsWalker::class);
        QueryUtil::addCustomTreeWalker($query, MergeConditionalExpressionWalker::class);

        $qb = $this->getQueryBuilder($query->getEntityManager(), $class, $alias);
        $queryAst = $this->injectFilter($qb, $class, $alias, $queryFilter)
            ->getQuery()
            ->getAST()
        ;

        /** @var Query\Parameter $param */
        foreach ($qb->getParameters()->toArray() as $param) {
            $query->setParameter($param->getName(), $param->getValue(), $param->getType());
        }

        if (MergeConditionalExpressionWalker::hasMergeableExpression($queryAst)) {
            MergeConditionalExpressionWalker::addHint($query, $queryAst);
        }

        $query->setHint(JoinsWalker::HINT_JOINS, $this->joins);
        $this->joins = [];
        $this->queryFields = [];
    }

    /**
     * Validate the node. Node is returned if it is valid.
     *
     * @param ConditionNode|NodeInterface|RuleNode $node  The node
     * @param ObjectMetadataInterface              $meta  The object metadata
     * @param string                               $alias The object alias
     */
    protected function validateNode(?NodeInterface $node, ObjectMetadataInterface $meta, string $alias): ?NodeInterface
    {
        $validNode = null;

        if ($node instanceof ConditionNode) {
            $validNode = $this->validateConditionNode($node, $meta, $alias);
        } elseif ($node instanceof RuleNode) {
            $validNode = $this->validateRuleNode($node, $meta, $alias);
        }

        return $validNode;
    }

    /**
     * Get the alias of object metadata name.
     *
     * @param ObjectMetadataInterface $metadata The object metadata
     */
    private function getNameAlias(ObjectMetadataInterface $metadata): string
    {
        $name = $metadata->getName();
        $exp = explode('_', $name);
        $alias = '';

        foreach ($exp as $part) {
            $alias .= substr($part, 0, 1);
        }

        return $alias;
    }

    /**
     * Get the request query filter.
     */
    private function getQueryFilter(): string
    {
        if ($request = $this->requestStack->getCurrentRequest()) {
            if ($request->headers->has('x-filter')) {
                return (string) $request->headers->get('x-filter', '');
            }

            return (string) $request->query->get('filter', '');
        }

        return '';
    }

    /**
     * Get the query builder.
     *
     * @param EntityManagerInterface $em    The entity manager
     * @param string                 $class The class name
     * @param string                 $alias The alias
     */
    private function getQueryBuilder(EntityManagerInterface $em, string $class, string $alias): QueryBuilder
    {
        return (new QueryBuilder($em))
            ->select($alias)
            ->from($class, $alias)
        ;
    }

    /**
     * Inject the filter in the query builder.
     *
     * @param QueryBuilder $qb          The query builder for filter
     * @param string       $class       The class
     * @param string       $alias       The alias
     * @param string       $queryFilter The request query filter
     */
    private function injectFilter(QueryBuilder $qb, string $class, string $alias, string $queryFilter): QueryBuilder
    {
        $meta = $this->metadataManager->get($class);
        $node = $this->validateNode($this->parser->parse($queryFilter), $meta, $alias);

        if (null === $node) {
            return $qb;
        }

        if (!$node->isValid()) {
            return $qb->andWhere($alias.'.id IS NULL');
        }

        foreach ($this->joins as $joinAlias => $joinConfig) {
            $qb->leftJoin($joinConfig['targetClass'], $joinAlias);
        }

        $filter = $node->compile(new CompileArgs(
            $qb->getParameters(),
            $qb->getEntityManager(),
            $this->metadataManager,
            $meta,
            $alias
        ));

        return $qb->where($filter);
    }

    /**
     * Validate the condition node.
     *
     * @param ConditionNode           $node  The condition node
     * @param ObjectMetadataInterface $meta  The object metadata
     * @param string                  $alias The object alias
     */
    private function validateConditionNode(ConditionNode $node, ObjectMetadataInterface $meta, string $alias): ?ConditionNode
    {
        $validNode = $node;
        $rules = [];

        foreach ($validNode->getRules() as $rule) {
            $rule = $this->validateNode($rule, $meta, $alias);

            if ($rule) {
                $rules[] = $rule;
            }
        }

        if (empty($rules)) {
            $validNode = null;
        } else {
            $validNode->setRules($rules);
        }

        return $validNode;
    }

    /**
     * Validate the rule node.
     *
     * @param RuleNode                $node  The condition node
     * @param ObjectMetadataInterface $meta  The object metadata
     * @param string                  $alias The object alias
     */
    private function validateRuleNode(RuleNode $node, ObjectMetadataInterface $meta, string $alias): ?RuleNode
    {
        $validNode = $node;
        $metaForField = $meta;
        $field = $validNode->getField();
        $joins = [];

        if (false !== strpos($field, '.')) {
            $links = explode('.', $field);
            $field = array_pop($links);
            $metaForField = QueryUtil::getAssociationMeta(
                $this->metadataManager,
                $meta,
                $links,
                $joins,
                $this->authChecker
            );
        }

        $fieldMeta = $metaForField && $metaForField->hasFieldByName($field)
            ? $metaForField->getFieldByName($field)
            : null;

        if (null === $fieldMeta) {
            $msgParams = [
                '{{ field }}' => $validNode->getField(),
            ];
            $msg = $this->translator->trans('doctrine_filterable.invalid_field', $msgParams, 'validators');
            $validNode->addError(new NodeError($msg, $msg, $msgParams));
        } elseif ($fieldMeta && $fieldMeta->isFilterable() && QueryUtil::isFieldVisible($metaForField, $fieldMeta, $this->authChecker)) {
            $field = $metaForField && $meta !== $metaForField
                ? QueryUtil::getAlias($metaForField).'.'.$fieldMeta->getField()
                : $alias.'.'.$fieldMeta->getField();
            $this->queryFields[$validNode->getField()] = $field;
            $this->joins = array_merge($joins, $this->joins);
            $this->validateRuleNodeOperator($validNode, $fieldMeta);
            $this->validateRuleNodeValue($validNode, $fieldMeta);
        } else {
            $validNode = null;
        }

        return $validNode;
    }

    /**
     * Validate the operator of rule node.
     *
     * @param RuleNode               $node      The rule node
     * @param FieldMetadataInterface $fieldMeta The field metadata
     */
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
        $form = $this->buildForm($node, $fieldMeta);
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
    private function buildForm(RuleNode $node, FieldMetadataInterface $fieldMeta): FormInterface
    {
        $maps = $this->parser->getMapForms();
        $type = $fieldMeta->getType();

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

    /**
     * Build the locked collection.
     *
     * @param RuleNode $node The rule node
     */
    private function buildLockedCollection(RuleNode $node): array
    {
        $data = [];

        for ($i = 0; $i < $node->getSizeCollection(); ++$i) {
            $data[] = null;
        }

        return $data;
    }
}
