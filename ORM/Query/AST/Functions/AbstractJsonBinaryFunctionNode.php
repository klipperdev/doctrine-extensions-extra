<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;

/**
 * Base of platform aware function node.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractJsonBinaryFunctionNode extends AbstractPlatformAwareFunctionNode
{
    public const JSON_DATA_KEY = 'jsonData';
    public const JSON_PATH_KEY = 'jsonPath';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameters[self::JSON_DATA_KEY] = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->parameters[self::JSON_PATH_KEY] = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
