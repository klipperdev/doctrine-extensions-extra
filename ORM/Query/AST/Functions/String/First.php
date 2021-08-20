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
 * First function.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class First extends AbstractPlatformAwareFunctionNode
{
    public const SUB_SELECT = 'sub_select';

    /**
     * @throws
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->parameters[self::SUB_SELECT] = $parser->Subselect();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
