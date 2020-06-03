<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\Transformers;

use Doctrine\ORM\Query\Parameter;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\CompileArgs;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\RuleNode;

/**
 * Filter node transformer of object.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class JsonGetTransformer implements NodeFieldNameTransformerInterface, NodeValueTransformerInterface
{
    private string $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function compileFieldName(CompileArgs $arguments, RuleNode $node, string $fieldName): string
    {
        $key = $this->getKey($arguments, 'path', $node->getField(), $this->key);

        return sprintf('JSON_GET(%s, :%s)', $fieldName, $key);
    }

    public function compileValue(CompileArgs $arguments, string $field): string
    {
        return ':'.$this->getKey($arguments, 'value', $field, $this->value);
    }

    /**
     * Set the parameter value in query parameters and return the key.
     *
     * @param CompileArgs $arguments  The query compile arguments
     * @param string      $type       The type
     * @param string      $field      The field
     * @param mixed       $paramValue The parameter value
     */
    private function getKey(CompileArgs $arguments, string $type, string $field, $paramValue): string
    {
        $pos = $arguments->getParameters()->count() + 1;
        $key = 'filter_'.str_replace(['.'], '_', $field).'_'.$type.'_'.$pos;
        $arguments->getParameters()->add(new Parameter($key, $paramValue));

        return $key;
    }
}
