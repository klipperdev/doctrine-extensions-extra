<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\String;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\AbstractPlatformAwareFunctionNode;

/**
 * String to array function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FindInSet extends AbstractPlatformAwareFunctionNode
{
    public const DATA_KEY = 'dataKey';
    public const DATA_VALUE = 'dataValue';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameters[self::DATA_VALUE] = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->parameters[self::DATA_KEY] = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
