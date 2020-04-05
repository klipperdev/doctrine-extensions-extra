<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Query\QueryException;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Platform\Functions\PlatformFunctionNode;

/**
 * Function factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class FunctionFactory
{
    /**
     * Create platform function node.
     *
     * @param string $platformName The platform name
     * @param string $functionName The function name
     * @param array  $parameters   The parameters
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public static function create($platformName, $functionName, array $parameters): PlatformFunctionNode
    {
        $className = __NAMESPACE__
            .'\\Platform\\Functions\\'
            .Inflector::classify(strtolower($platformName))
            .'\\'
            .Inflector::classify(strtolower($functionName));

        if (!class_exists($className)) {
            throw QueryException::syntaxError(
                sprintf(
                    'Function "%s" does not supported for platform "%s"',
                    $functionName,
                    $platformName
                )
            );
        }

        return new $className($parameters);
    }
}
