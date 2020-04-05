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

use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\AbstractPlatformAwareFunctionNode;

/**
 * Cast function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Cast extends AbstractPlatformAwareFunctionNode
{
    public const PARAMETER_KEY = 'expression';
    public const TYPE_KEY = 'type';

    /**
     * @var string[]
     */
    protected static $supportedTypes = [
        'char',
        'string',
        'text',
        'date',
        'datetime',
        'time',
        'int',
        'integer',
        'decimal',
        'json',
        'bool',
        'boolean',
    ];

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameters[self::PARAMETER_KEY] = $parser->ArithmeticExpression();

        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $type = $lexer->token['value'];

        if ($lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $parser->match(Lexer::T_OPEN_PARENTHESIS);
            /** @var Literal $parameter */
            $parameter = $parser->Literal();
            $parameters = [
                $parameter->value,
            ];
            if ($lexer->isNextToken(Lexer::T_COMMA)) {
                while ($lexer->isNextToken(Lexer::T_COMMA)) {
                    $parser->match(Lexer::T_COMMA);
                    $parameter = $parser->Literal();
                    $parameters[] = $parameter->value;
                }
            }
            $parser->match(Lexer::T_CLOSE_PARENTHESIS);
            $type .= '('.implode(', ', $parameters).')';
        }

        if (!$this->checkType($type)) {
            $parser->syntaxError(
                sprintf(
                    'Type unsupported. Supported types are: "%s"',
                    implode(', ', static::$supportedTypes)
                ),
                $lexer->token
            );
        }

        $this->parameters[self::TYPE_KEY] = $type;

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Check that given type is supported.
     *
     * @param string $type The type
     */
    protected function checkType(string $type): bool
    {
        $type = strtolower(trim($type));

        foreach (static::$supportedTypes as $supportedType) {
            if (0 === strpos($type, $supportedType)) {
                return true;
            }
        }

        return false;
    }
}
