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

use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\AST\JoinAssociationDeclaration;
use Doctrine\ORM\Query\AST\JoinAssociationPathExpression;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\TreeWalkerAdapter;

/**
 * Joins Query TreeWalker.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JoinsWalker extends TreeWalkerAdapter
{
    public const HINT_JOINS = 'klipper_doctrine_extensions_extra.walker.joins';

    /**
     * @throws
     */
    public function walkSelectStatement(SelectStatement $AST): void
    {
        $query = $this->_getQuery();
        $metaAliases = $query->getHint(self::HINT_JOINS);

        if (!\is_array($metaAliases) || empty($metaAliases)) {
            return;
        }

        $requiredKeys = ['targetClass', 'parentClass', 'relation'];
        $components = $this->_getQueryComponents();
        /** @var IdentificationVariableDeclaration $idVarDeclaration */
        $idVarDeclaration = $AST->fromClause->identificationVariableDeclarations[0];
        $aliasIdVar = $idVarDeclaration->rangeVariableDeclaration->aliasIdentificationVariable;
        $em = $this->_getQuery()->getEntityManager();
        $rootComponent = $components[$aliasIdVar];

        foreach ($metaAliases as $metaAlias => $config) {
            if (!isset($components[$metaAlias])) {
                if (array_diff($requiredKeys, array_keys($config))) {
                    throw new QueryException('Invalid joins config of klipper doctrine order by.
                        requires "'.implode('", "', $requiredKeys).'"');
                }

                $class = $em->getClassMetadata($config['parentClass']);
                $targetClass = $em->getClassMetadata($config['targetClass']);
                $joinQueryComponent = [
                    'metadata' => $targetClass,
                    'parent' => $class,
                    'relation' => $class->getAssociationMapping($config['relation']),
                    'map' => null,
                    'nestingLevel' => $rootComponent['nestingLevel'],
                    'token' => $rootComponent['token'],
                ];
                $this->setQueryComponent($metaAlias, $joinQueryComponent);
                $components = $this->getQueryComponents();

                $idVarDeclaration->joins[] = new Join(
                    Join::JOIN_TYPE_LEFT,
                    new JoinAssociationDeclaration(
                        new JoinAssociationPathExpression(
                            $aliasIdVar,
                            $config['relation']
                        ),
                        $metaAlias,
                        null
                    )
                );
            }
        }
    }
}
