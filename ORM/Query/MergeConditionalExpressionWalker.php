<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\ConditionalExpression;
use Doctrine\ORM\Query\AST\ConditionalFactor;
use Doctrine\ORM\Query\AST\ConditionalPrimary;
use Doctrine\ORM\Query\AST\ConditionalTerm;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\AST\JoinAssociationDeclaration;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\WhereClause;
use Doctrine\ORM\Query\TreeWalkerAdapter;

/**
 * TreeWalker to merge conditional expression in where clause and from join with the
 * conditional expressions in query.
 *
 * To work with joins, the Query Hint of Klipper\Component\DoctrineExtensionsExtra\ORM\Query\JoinsWalker must be used.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MergeConditionalExpressionWalker extends TreeWalkerAdapter
{
    /**
     * @var null|SelectStatement
     */
    public const HINT_MERGE_AST = 'klipper_doctrine_extensions_extra.walker.merge_conditional_expression';

    /**
     * Add the new merge AST in query hint.
     *
     * @param Query           $query  The query
     * @param SelectStatement $newAst The doctrine AST of new query
     */
    public static function addHint(Query $query, SelectStatement $newAst): void
    {
        $mergeAST = $query->getHint(static::HINT_MERGE_AST);

        if (\is_array($mergeAST)) {
            $hintAst = array_merge($mergeAST, [$newAst]);
        } elseif ($mergeAST instanceof SelectStatement) {
            $hintAst = [$mergeAST, $newAst];
        } else {
            $hintAst = [$newAst];
        }

        $query->setHint(self::HINT_MERGE_AST, $hintAst);
    }

    /**
     * Check if the new query has a mergeable conditional expression.
     *
     * @param mixed|SelectStatement|SelectStatement[] $AST The doctrine AST of new query
     */
    public static function hasMergeableExpression($AST): bool
    {
        if (\is_array($AST)) {
            $mergeable = false;

            foreach ($AST as $itemAST) {
                if (static::hasMergeableExpression($itemAST)) {
                    $mergeable = true;

                    break;
                }
            }

            return $mergeable;
        }

        if ($AST instanceof SelectStatement) {
            if ($AST->whereClause instanceof WhereClause) {
                return true;
            }

            if ($AST->fromClause instanceof FromClause) {
                /** @var IdentificationVariableDeclaration $declaration */
                foreach ($AST->fromClause->identificationVariableDeclarations as $declaration) {
                    if (\count($declaration->joins) > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function walkSelectStatement(SelectStatement $AST): void
    {
        /** @var mixed|SelectStatement|SelectStatement[] $newASTs */
        $newASTs = $this->_getQuery()->getHint(self::HINT_MERGE_AST);

        if (!static::hasMergeableExpression($newASTs)) {
            return;
        }

        $newASTs = (array) $newASTs;

        foreach ($newASTs as $newAST) {
            $this->addConditionalInWhereClause($AST, $newAST);
            $this->addConditionalInFromClauseJoin($AST, $newAST);
        }
    }

    /**
     * Add the new query where conditional expression in the where clause of query.
     *
     * @param SelectStatement $AST    The select statement AST of query
     * @param SelectStatement $newAST The select statement AST of new query
     */
    private function addConditionalInWhereClause(SelectStatement $AST, SelectStatement $newAST): void
    {
        if (!$newAST->whereClause instanceof WhereClause) {
            return;
        }

        if ($AST->whereClause instanceof WhereClause) {
            $this->mergeConditionals($AST->whereClause, $newAST->whereClause->conditionalExpression);
        } elseif (null !== $conditional = $this->getConditionalForEmptyCondition($newAST->whereClause->conditionalExpression)) {
            $AST->whereClause = new WhereClause($conditional);
        }
    }

    /**
     * Add the new query join conditional expression in the from clause join of query.
     *
     * @param SelectStatement $AST    The select statement AST of query
     * @param SelectStatement $newAST The select statement AST of new query
     */
    private function addConditionalInFromClauseJoin(SelectStatement $AST, SelectStatement $newAST): void
    {
        if ($AST->fromClause instanceof FromClause && $newAST->fromClause instanceof FromClause) {
            $mapJoinDeclarations = $this->getJoinDeclarationMap($AST->fromClause);

            /** @var IdentificationVariableDeclaration $newDeclaration */
            foreach ($newAST->fromClause->identificationVariableDeclarations as $newDeclaration) {
                if (null !== $newDeclaration->rangeVariableDeclaration) {
                    $range = $newDeclaration->rangeVariableDeclaration;

                    /** @var Join $newJoin */
                    foreach ($newDeclaration->joins as $newJoin) {
                        if (null !== $newJoin->joinAssociationDeclaration) {
                            /** @var JoinAssociationDeclaration $newJoinDec */
                            $newJoinDec = $newJoin->joinAssociationDeclaration;

                            if (isset($mapJoinDeclarations[$range->abstractSchemaName][$newJoinDec->aliasIdentificationVariable])) {
                                /** @var Join $existingJoin */
                                $existingJoin = $mapJoinDeclarations[$range->abstractSchemaName][$newJoinDec->aliasIdentificationVariable];

                                if (null !== $existingJoin->conditionalExpression) {
                                    $existingJoin->joinType = Join::JOIN_TYPE_INNER;
                                    $this->mergeConditionals($existingJoin, $newJoin->conditionalExpression);
                                } elseif (null !== $conditional = $this->getConditionalForEmptyCondition($newJoin->conditionalExpression)) {
                                    $existingJoin->joinType = Join::JOIN_TYPE_INNER;
                                    $existingJoin->conditionalExpression = $conditional;
                                }
                            } else {
                                $newJoin->joinType = Join::JOIN_TYPE_INNER;

                                /** @var IdentificationVariableDeclaration $declaration */
                                foreach ($AST->fromClause->identificationVariableDeclarations as $declaration) {
                                    $declaration->joins[] = $newJoin;

                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the map of join declarations from query.
     *
     * @param FromClause $fromClause The from clause
     */
    private function getJoinDeclarationMap(FromClause $fromClause): array
    {
        $mapJoinDeclarations = [];

        /** @var IdentificationVariableDeclaration $declaration */
        foreach ($fromClause->identificationVariableDeclarations as $declaration) {
            if (null !== $declaration->rangeVariableDeclaration) {
                $range = $declaration->rangeVariableDeclaration;

                /** @var Join $join */
                foreach ($declaration->joins as $index => $join) {
                    if (null !== $join->joinAssociationDeclaration) {
                        /** @var JoinAssociationDeclaration $joinDec */
                        $joinDec = $join->joinAssociationDeclaration;
                        $joinAlias = $joinDec->aliasIdentificationVariable;
                        $mapJoinDeclarations[$range->abstractSchemaName][$joinAlias] = $join;
                    }
                }
            }
        }

        return $mapJoinDeclarations;
    }

    /**
     * Merge the new conditional expression with the existing conditional expression.
     *
     * @param Join|WhereClause $queryNode      The where clause AST node or from join AST node of query
     * @param Node             $newConditional The AST Node of the new conditional expression
     */
    private function mergeConditionals($queryNode, Node $newConditional): void
    {
        if ($queryNode->conditionalExpression instanceof ConditionalTerm) {
            $this->addConditionalInTerm($queryNode, $newConditional);
        } elseif ($queryNode->conditionalExpression instanceof ConditionalPrimary) {
            $this->addConditionalInPrimary($queryNode, $newConditional);
        } elseif ($queryNode->conditionalExpression instanceof ConditionalExpression
                || $queryNode->conditionalExpression instanceof ConditionalFactor) {
            $this->addConditionalInExpressionOrFactor($queryNode, $newConditional);
        }
    }

    /**
     * Get the conditional expression for the empty where clause from the new conditional expression.
     *
     * @param Node $newConditional The AST Node of the new conditional expression
     *
     * @return null|ConditionalExpression|ConditionalFactor|Node
     */
    private function getConditionalForEmptyCondition(Node $newConditional)
    {
        $conditional = null;

        if ($newConditional instanceof ConditionalTerm) {
            $conditional = new ConditionalExpression([
                $newConditional,
            ]);
        } elseif ($newConditional instanceof ConditionalPrimary) {
            $conditional = new ConditionalExpression([
                new ConditionalTerm([
                    $newConditional,
                ]),
            ]);
        } elseif ($newConditional instanceof ConditionalExpression
                || $newConditional instanceof ConditionalFactor) {
            $conditional = $newConditional;
        }

        return $conditional;
    }

    /**
     * Add the new conditional expression in existing conditional term.
     *
     * @param Join|WhereClause $queryNode      The where clause AST node or from join AST node of query
     * @param Node             $newConditional The AST Node of the new conditional expression
     */
    private function addConditionalInTerm($queryNode, Node $newConditional): void
    {
        /* @var ConditionalTerm $queryNode->conditionalExpression */

        if ($newConditional instanceof ConditionalTerm) {
            $queryNode->conditionalExpression->conditionalFactors = array_merge(
                $queryNode->conditionalExpression->conditionalFactors,
                $newConditional->conditionalFactors
            );
        } elseif ($newConditional instanceof ConditionalPrimary) {
            $queryNode->conditionalExpression->conditionalFactors[] = $newConditional;
        } elseif ($newConditional instanceof ConditionalExpression
                || $newConditional instanceof ConditionalFactor) {
            $tmpPrimaryNew = new ConditionalPrimary();
            $tmpPrimaryNew->conditionalExpression = $newConditional;
            $queryNode->conditionalExpression->conditionalFactors[] = $tmpPrimaryNew;
        }
    }

    /**
     * Add the new conditional expression in existing conditional primary.
     *
     * @param Join|WhereClause $queryNode      The where clause AST node or from join AST node of query
     * @param Node             $newConditional The AST Node of the new conditional expression
     */
    private function addConditionalInPrimary($queryNode, Node $newConditional): void
    {
        /* @var ConditionalPrimary $queryNode->conditionalExpression */

        if ($newConditional instanceof ConditionalTerm) {
            $newConditional->conditionalFactors[] = $queryNode->conditionalExpression;
            $queryNode->conditionalExpression = new ConditionalExpression([
                $newConditional,
            ]);
        } elseif ($newConditional instanceof ConditionalPrimary) {
            $queryNode->conditionalExpression = new ConditionalExpression([
                new ConditionalTerm([
                    $queryNode->conditionalExpression,
                    $newConditional,
                ]),
            ]);
        } elseif ($newConditional instanceof ConditionalExpression
                || $newConditional instanceof ConditionalFactor) {
            $tmpPrimaryNew = new ConditionalPrimary();
            $tmpPrimaryNew->conditionalExpression = $newConditional;
            $queryNode->conditionalExpression = new ConditionalTerm([
                $queryNode->conditionalExpression,
                $tmpPrimaryNew,
            ]);
        }
    }

    /**
     * Add the new conditional expression in existing conditional expression or conditional factor.
     *
     * @param Join|WhereClause $queryNode      The where clause AST node or from join AST node of query
     * @param Node             $newConditional The AST Node of the new conditional expression
     */
    private function addConditionalInExpressionOrFactor($queryNode, Node $newConditional): void
    {
        /* @var ConditionalExpression|ConditionalFactor $queryNode->conditionalExpression */

        if ($newConditional instanceof ConditionalTerm
                || $newConditional instanceof ConditionalExpression
                || $newConditional instanceof ConditionalFactor) {
            $tmpPrimaryQuery = new ConditionalPrimary();
            $tmpPrimaryQuery->conditionalExpression = $queryNode->conditionalExpression;
            $tmpPrimaryNew = new ConditionalPrimary();
            $tmpPrimaryNew->conditionalExpression = $newConditional;
            $queryNode->conditionalExpression = new ConditionalTerm([
                $tmpPrimaryQuery,
                $tmpPrimaryNew,
            ]);
        } elseif ($newConditional instanceof ConditionalPrimary) {
            $tmpPrimaryQuery = new ConditionalPrimary();
            $tmpPrimaryQuery->conditionalExpression = $queryNode->conditionalExpression;
            $queryNode->conditionalExpression = new ConditionalTerm([
                $tmpPrimaryQuery,
                $newConditional,
            ]);
        }
    }
}
