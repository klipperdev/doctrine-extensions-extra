<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\Numeric;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Klipper\Component\DoctrineExtensionsExtra\ORM\Query\AST\Functions\AbstractPlatformAwareFunctionNode;

/**
 * Convert surface function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConvertSurface extends AbstractPlatformAwareFunctionNode
{
    public const SURFACE_FIELD = 'surface_field';
    public const SURFACE_UNIT_FIELD = 'surface_unit_field';
    public const UNIT_CONVERSION = 'unit_conversion';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameters[self::SURFACE_FIELD] = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->parameters[self::SURFACE_UNIT_FIELD] = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->parameters[self::UNIT_CONVERSION] = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
