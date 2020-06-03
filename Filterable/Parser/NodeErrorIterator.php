<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\BadMethodCallException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\InvalidArgumentException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Exception\OutOfBoundsException;
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;

/**
 * Iterates over the errors of a node.
 *
 * Optionally, this class supports recursive iteration. In order to iterate
 * recursively, set the constructor argument $deep to true. Now each element
 * returned by the iterator is either an instance of {@link NodeError} or of
 * {@link NodeErrorIterator}, in case the errors belong to a sub-node.
 *
 * You can also wrap the iterator into a {@link \RecursiveIteratorIterator} to
 * flatten the recursive structure into a flat list of errors.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class NodeErrorIterator implements \RecursiveIterator, \SeekableIterator, \ArrayAccess, \Countable
{
    /**
     * The prefix used for indenting nested error messages.
     *
     * @var string
     */
    public const INDENTATION = '    ';

    private NodeInterface $node;

    /**
     * @var NodeError[]|NodeErrorIterator[]
     */
    private array $errors;

    /**
     * Creates a new iterator.
     *
     * @param NodeInterface $node   The erroneous node
     * @param array         $errors The node errors
     *
     * @throws InvalidArgumentException If the errors are invalid
     */
    public function __construct(NodeInterface $node, array $errors)
    {
        foreach ($errors as $error) {
            if (!($error instanceof NodeError || $error instanceof self)) {
                throw new InvalidArgumentException(sprintf(
                    'The errors must be instances of '.
                    '"\%s" or "%s". Got: "%s".',
                    NodeError::class,
                    __CLASS__,
                    \is_object($error) ? \get_class($error) : \gettype($error)
                ));
            }
        }

        $this->node = $node;
        $this->errors = $errors;
    }

    /**
     * Returns all iterated error messages as string.
     *
     * @return string The iterated error messages
     */
    public function __toString(): string
    {
        $string = '';

        foreach ($this->errors as $error) {
            if ($error instanceof NodeError) {
                $string .= 'ERROR: '.$error->getMessage()."\n";
            } else {
                /* @var $error NodeErrorIterator */
                $string .= $error->node->getName().":\n";
                $string .= self::indent((string) $error);
            }
        }

        return $string;
    }

    /**
     * Returns the iterated node.
     *
     * @return NodeInterface The node whose errors are iterated by this object
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    /**
     * Returns the current element of the iterator.
     *
     * @return NodeError|NodeErrorIterator an error or an iterator containing
     *                                     nested errors
     */
    public function current()
    {
        return current($this->errors);
    }

    /**
     * Advances the iterator to the next position.
     */
    public function next(): void
    {
        next($this->errors);
    }

    /**
     * Returns the current position of the iterator.
     *
     * @return int The 0-indexed position
     */
    public function key(): int
    {
        return key($this->errors);
    }

    /**
     * Returns whether the iterator's position is valid.
     *
     * @return bool Whether the iterator is valid
     */
    public function valid(): bool
    {
        return null !== key($this->errors);
    }

    /**
     * Sets the iterator's position to the beginning.
     *
     * This method detects if errors have been added to the node since the
     * construction of the iterator.
     */
    public function rewind(): void
    {
        reset($this->errors);
    }

    /**
     * Returns whether a position exists in the iterator.
     *
     * @param int $position The position
     *
     * @return bool Whether that position exists
     */
    public function offsetExists($position): bool
    {
        return isset($this->errors[$position]);
    }

    /**
     * Returns the element at a position in the iterator.
     *
     * @param int $position The position
     *
     * @throws OutOfBoundsException If the given position does not exist
     *
     * @return NodeError|NodeErrorIterator The element at the given position
     */
    public function offsetGet($position)
    {
        if (!isset($this->errors[$position])) {
            throw new OutOfBoundsException('The offset '.$position.' does not exist.');
        }

        return $this->errors[$position];
    }

    /**
     * Unsupported method.
     *
     * @param mixed $position
     * @param mixed $value
     *
     * @throws BadMethodCallException
     */
    public function offsetSet($position, $value): void
    {
        throw new BadMethodCallException('The iterator doesn\'t support modification of elements.');
    }

    /**
     * Unsupported method.
     *
     * @param mixed $position
     *
     * @throws BadMethodCallException
     */
    public function offsetUnset($position): void
    {
        throw new BadMethodCallException('The iterator doesn\'t support modification of elements.');
    }

    /**
     * Returns whether the current element of the iterator can be recursed
     * into.
     *
     * @return bool Whether the current element is an instance of this class
     */
    public function hasChildren(): bool
    {
        return current($this->errors) instanceof self;
    }

    /**
     * Alias of {@link current()}.
     */
    public function getChildren()
    {
        return current($this->errors);
    }

    /**
     * Returns the number of elements in the iterator.
     *
     * Note that this is not the total number of errors, if the constructor
     * parameter $deep was set to true! In that case, you should wrap the
     * iterator into a {@link \RecursiveIteratorIterator} with the standard mode
     * {@link \RecursiveIteratorIterator::LEAVES_ONLY} and count the result.
     *
     *     $iterator = new \RecursiveIteratorIterator($node->getErrors(true));
     *     $count = count(iterator_to_array($iterator));
     *
     * Alternatively, set the constructor argument $flatten to true as well.
     *
     *     $count = count($node->getErrors(true, true));
     *
     * @return int The number of iterated elements
     */
    public function count(): int
    {
        return \count($this->errors);
    }

    /**
     * Sets the position of the iterator.
     *
     * @param int $position The new position
     *
     * @throws OutOfBoundsException If the position is invalid
     */
    public function seek($position): void
    {
        if (!isset($this->errors[$position])) {
            throw new OutOfBoundsException('The offset '.$position.' does not exist.');
        }

        reset($this->errors);

        while ($position !== key($this->errors)) {
            next($this->errors);
        }
    }

    /**
     * Utility function for indenting multi-line strings.
     *
     * @param string $string The string
     *
     * @return string The indented string
     */
    private static function indent(string $string): string
    {
        return rtrim(self::INDENTATION.str_replace("\n", "\n".self::INDENTATION, $string), ' ');
    }
}
