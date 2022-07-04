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
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\FilterInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\ConditionNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\NodeError;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Parser;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\JoinsWalker;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\MergeConditionalExpressionWalker;
use Klipper\Component\DoctrineExtensionsExtra\Util\QueryUtil;
use Klipper\Component\Metadata\AssociationMetadataInterface;
use Klipper\Component\Metadata\FieldMetadataInterface;
use Klipper\Component\Metadata\MetadataManagerInterface;
use Klipper\Component\Metadata\ObjectMetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FilterableQuery implements FilterableQueryInterface
{
    private EntityManagerInterface $em;

    private MetadataManagerInterface $metadataManager;

    private Parser $parser;

    private FormFactoryInterface $formFactory;

    private TranslatorInterface $translator;

    private ?ExpressionLanguage $expressionLanguage;

    private ?AuthorizationCheckerInterface $authChecker;

    /**
     * @var FilterFormGuesserInterface[]
     */
    private array $formGuessers;

    /**
     * @param EntityManagerInterface             $em                 The entity manager
     * @param MetadataManagerInterface           $metadataManager    The metadata manager
     * @param Parser                             $parser             The parser of filterable
     * @param FormFactoryInterface               $formFactory        The form factory
     * @param TranslatorInterface                $translator         The translator
     * @param null|ExpressionLanguage            $expressionLanguage The expression language
     * @param null|AuthorizationCheckerInterface $authChecker        The authorization checker
     * @param FilterFormGuesserInterface[]       $formGuessers       The form guessers
     */
    public function __construct(
        EntityManagerInterface $em,
        MetadataManagerInterface $metadataManager,
        Parser $parser,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ?ExpressionLanguage $expressionLanguage = null,
        ?AuthorizationCheckerInterface $authChecker = null,
        array $formGuessers = []
    ) {
        $this->em = $em;
        $this->metadataManager = $metadataManager;
        $this->parser = $parser;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->expressionLanguage = $expressionLanguage;
        $this->authChecker = $authChecker;
        $this->formGuessers = $formGuessers;
    }

    public function validate(string $metadataName, $filter, bool $forceFirstCondition = false): NodeInterface
    {
        $meta = $this->metadataManager->getByName($metadataName);
        $joins = [];

        $node = $this->parser->parse($filter, $forceFirstCondition);
        $this->validateNode($node, $meta, null, self::VALIDATE_VALUE, $joins);

        return $node;
    }

    public function filter(Query $query, $filter, int $validate = self::VALIDATE_NONE): Query
    {
        if (null === $filter) {
            return $query;
        }

        /** @var Query\AST\IdentificationVariableDeclaration[] $varDeclarations */
        $varDeclarations = $query->getAST()->fromClause->identificationVariableDeclarations;

        foreach ($varDeclarations as $varDeclaration) {
            $rangeDeclaration = $varDeclaration->rangeVariableDeclaration;
            $class = $rangeDeclaration->abstractSchemaName;

            if ($rangeDeclaration->isRoot && $this->metadataManager->has($class)) {
                $this->doFilter($query, $class, $rangeDeclaration->aliasIdentificationVariable, $filter, $validate);

                break;
            }
        }

        return $query;
    }

    /**
     * Filter the query.
     *
     * @param Query                               $query    The query
     * @param string                              $class    The root class name
     * @param string                              $alias    The alias
     * @param array|FilterInterface|NodeInterface $filter   The filter
     * @param int                                 $validate Check if filter must be validate
     */
    private function doFilter(Query $query, string $class, string $alias, $filter, int $validate): void
    {
        QueryUtil::addCustomTreeWalker($query, JoinsWalker::class);
        QueryUtil::addCustomTreeWalker($query, MergeConditionalExpressionWalker::class);

        $joins = [];
        $qb = $this->getQueryBuilder($query->getEntityManager(), $class, $alias);
        $queryAst = $this->injectFilter($qb, $class, $alias, $filter, $query, $validate, $joins)
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

        JoinsWalker::addHint($query, $joins);
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
     * @param QueryBuilder        $qb            The query builder for filter
     * @param string              $class         The class
     * @param string              $alias         The alias
     * @param array|NodeInterface $filter        The filter
     * @param Query               $originalQuery The original query
     * @param int                 $validate      Check if filter must be validate
     * @param array               $joins         The joins
     */
    private function injectFilter(
        QueryBuilder $qb,
        string $class,
        string $alias,
        $filter,
        Query $originalQuery,
        int $validate,
        array &$joins
    ): QueryBuilder {
        $node = $filter instanceof NodeInterface ? $filter : $this->parser->parse($filter);
        $meta = $this->metadataManager->get($class);
        $node = $this->validateNode($node, $meta, $alias, $validate, $joins);

        if (null === $node) {
            return $qb;
        }

        if (!$node->isValid()) {
            return $qb->andWhere($alias.'.id IS NULL');
        }

        QueryUtil::injectOriginalJoins($qb, $originalQuery);

        foreach ($joins as $joinAlias => $joinConfig) {
            $qb->leftJoin($joinConfig['joinAssociation'], $joinAlias);
        }

        $filter = $node->compile(new CompileArgs(
            $qb->getParameters(),
            $qb->getEntityManager(),
            $this->metadataManager,
            $meta,
            $alias,
            QueryUtil::getJoinAliases($qb)
        ));

        return $qb->where($filter);
    }

    /**
     * Validate the node. Node is returned if it is valid.
     *
     * @param ConditionNode|NodeInterface|RuleNode $node  The node
     * @param ObjectMetadataInterface              $meta  The object metadata
     * @param null|string                          $alias The alias of object metadata
     * @param array                                $joins The joins
     */
    private function validateNode(
        ?NodeInterface $node,
        ObjectMetadataInterface $meta,
        ?string $alias,
        int $validate,
        array &$joins
    ): ?NodeInterface {
        $validNode = null;

        if ($node instanceof ConditionNode) {
            $validNode = $this->validateConditionNode($node, $meta, $alias, $validate, $joins);
        } elseif ($node instanceof RuleNode) {
            $validNode = $this->validateRuleNode($node, $meta, $alias, $validate, $joins);
        }

        return $validNode;
    }

    /**
     * Validate the condition node.
     *
     * @param ConditionNode           $node  The condition node
     * @param ObjectMetadataInterface $meta  The object metadata
     * @param null|string             $alias The alias of object metadata
     * @param array                   $joins The joins
     */
    private function validateConditionNode(
        ConditionNode $node,
        ObjectMetadataInterface $meta,
        ?string $alias,
        int $validate,
        array &$joins
    ): ?ConditionNode {
        $validNode = $node;
        $rules = [];

        foreach ($validNode->getRules() as $rule) {
            $rule = $this->validateNode($rule, $meta, $alias, $validate, $joins);

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
     * @param null|string             $alias The alias of object metadata
     * @param array                   $joins The joins
     */
    private function validateRuleNode(
        RuleNode $node,
        ObjectMetadataInterface $meta,
        ?string $alias,
        bool $validate,
        array &$joins
    ): ?RuleNode {
        $validNode = $node;
        $metaForField = $meta;
        $field = $validNode->getField();
        $nodeJoins = [];

        if (false !== strpos($field, '.')) {
            $links = explode('.', $field);
            $field = array_pop($links);
            $metaForField = QueryUtil::getAssociationMeta(
                $this->metadataManager,
                $meta,
                $links,
                $nodeJoins,
                $validate >= self::VALIDATE_ALL ? $this->authChecker : null,
                $alias
            );
        }

        $fieldMeta = $metaForField && $metaForField->hasFieldByName($field)
            ? $metaForField->getFieldByName($field)
            : null;

        if (null === $fieldMeta && null !== $metaForField && $metaForField->hasAssociationByName($field)) {
            $fieldMeta = $metaForField->getAssociationByName($field);
        }

        if (null === $fieldMeta) {
            $msgParams = [
                '{{ field }}' => $validNode->getField(),
            ];
            $msg = $this->translator->trans('doctrine_filterable.invalid_field', $msgParams, 'validators');
            $validNode->addError(new NodeError($msg, $msg, $msgParams));
        } elseif ($fieldMeta instanceof FieldMetadataInterface && $fieldMeta->isFilterable()
            && ($validate < self::VALIDATE_ALL || QueryUtil::isFieldVisible($metaForField, $fieldMeta, $this->authChecker))
        ) {
            $joins = array_merge($nodeJoins, $joins);
            $this->validateRuleNodeOperator($validNode, $fieldMeta);

            if ($validate >= self::VALIDATE_VALUE) {
                $this->validateRuleNodeValue($validNode, $fieldMeta);
            }
        } elseif (!$fieldMeta instanceof AssociationMetadataInterface) {
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
            $classMeta = $this->em->getClassMetadata($fieldMeta->getParent()->getClass());
            $type = $classMeta->getFieldMapping($fieldMeta->getField())['type'];
            $value = $this->em->getConnection()->convertToDatabaseValue($form->getData(), $type);

            $node->setQueryValue($value);
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
        $type = $this->getFieldType($fieldMeta);

        if (isset($maps[$type])) {
            $formTypeConfig = (array) $maps[$type];
            $formType = $formTypeConfig[0];
            $options = isset($formTypeConfig[1]) && \is_array($formTypeConfig[1]) ? $formTypeConfig[1] : [];
            $options['csrf_protection'] = false;
            $formConfig = new FilterFormConfig($formType, $options);

            foreach ($this->formGuessers as $formGuesser) {
                $formGuesser->guess($formConfig, $node, $fieldMeta);
            }

            $formType = $formConfig->getType();
            $options = $formConfig->getOptions();

            if ($node->isRequiredValue()) {
                $options['constraints'][] = new NotNull();
            }

            if ($node->isCollectible()) {
                $lockedCollection = $node->getSizeCollection() > 0;
                $data = $lockedCollection ? $this->buildLockedCollection($node) : null;

                $formBuilder = $this->formFactory->createBuilder(CollectionType::class, $data, [
                    'entry_type' => $formType,
                    'entry_options' => $options,
                    'allow_add' => !$lockedCollection,
                    'allow_delete' => !$lockedCollection,
                    'prototype' => false,
                    'csrf_protection' => false,
                    'constraints' => [new Valid()],
                ]);

                if (!$lockedCollection) {
                    $formBuilder->addEventSubscriber(new CollectibleSubscriber());
                }
            } else {
                $formBuilder = $this->formFactory->createBuilder($formType, null, $options);
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

    /**
     * Get the type of the field metadata.
     *
     * @param FieldMetadataInterface $fieldMeta The field metadata
     */
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
