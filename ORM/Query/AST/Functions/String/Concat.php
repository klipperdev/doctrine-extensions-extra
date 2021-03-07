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
 * Concat function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Concat extends AbstractPlatformAwareFunctionNode
{
    public const VALUES_KEY = 'values';
    public const NOT_EMPTY_KEY = 'not_empty';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $values = [];
        $notEmpty = false;

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        // Add the concat separator to the values array.
        $values[] = $parser->ArithmeticExpression();

        // Add the rest of the strings to the values array. CONCAT_WS must
        // be used with at least 2 strings not including the separator.

        $lexer = $parser->getLexer();

        while (\count($values) < 3 || Lexer::T_COMMA === $lexer->lookahead['type']) {
            $parser->match(Lexer::T_COMMA);
            $peek = $lexer->glimpse();

            $values[] = '(' === $peek['value']
                ? $parser->FunctionDeclaration()
                : $parser->ArithmeticExpression();
        }

        while (Lexer::T_IDENTIFIER === $lexer->lookahead['type']) {
            switch (strtolower($lexer->lookahead['value'])) {
                case 'notempty':
                    $parser->match(Lexer::T_IDENTIFIER);
                    $notEmpty = true;

                    break;

                default: // Identifier not recognized (causes exception).
                    $parser->match(Lexer::T_CLOSE_PARENTHESIS);

                    break;
            }
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        $this->parameters[self::VALUES_KEY] = $values;
        $this->parameters[self::NOT_EMPTY_KEY] = $notEmpty;
    }
}
