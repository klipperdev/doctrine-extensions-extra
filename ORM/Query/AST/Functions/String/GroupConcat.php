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
 * Group Concat function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class GroupConcat extends AbstractPlatformAwareFunctionNode
{
    public const PARAMETER_KEY = 'expression';

    public const ORDER_KEY = 'order';

    public const SEPARATOR_KEY = 'separator';

    public const DISTINCT_KEY = 'distinct';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $lexer = $parser->getLexer();
        if ($lexer->isNextToken(Lexer::T_DISTINCT)) {
            $parser->match(Lexer::T_DISTINCT);

            $this->parameters[self::DISTINCT_KEY] = true;
        }

        // first Path Expression is mandatory
        $this->parameters[self::PARAMETER_KEY] = [];
        $this->parameters[self::PARAMETER_KEY][] = $parser->StringPrimary();

        while ($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->parameters[self::PARAMETER_KEY][] = $parser->StringPrimary();
        }

        if ($lexer->isNextToken(Lexer::T_ORDER)) {
            $this->parameters[self::ORDER_KEY] = $parser->OrderByClause();
        }

        if ($lexer->isNextToken(Lexer::T_IDENTIFIER)) {
            if ('separator' !== strtolower($lexer->lookahead['value'])) {
                $parser->syntaxError('separator');
            }
            $parser->match(Lexer::T_IDENTIFIER);

            $this->parameters[self::SEPARATOR_KEY] = $parser->StringPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
