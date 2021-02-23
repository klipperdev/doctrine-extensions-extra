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
use Klipper\Component\DoctrineExtensionsExtra\Filterable\Parser\Node\NodeInterface;

/**
 * Wraps errors in node.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class NodeError implements \Serializable
{
    /**
     * The template for the error message.
     */
    protected string $messageTemplate;

    /**
     * The parameters that should be substituted in the message template.
     */
    protected array $messageParameters;

    /**
     * The value for error message pluralization.
     */
    protected ?int $messagePluralization;

    private string $message;

    /**
     * The cause for this error.
     *
     * @var null|mixed
     */
    private $cause;

    /**
     * The node that spawned this error.
     */
    private ?NodeInterface $origin = null;

    /**
     * Any array key in $messageParameters will be used as a placeholder in
     * $messageTemplate.
     *
     * @param string      $message              The translated error message
     * @param null|string $messageTemplate      The template for the error message
     * @param array       $messageParameters    The parameters that should be
     *                                          substituted in the message template
     * @param null|int    $messagePluralization The value for error message pluralization
     * @param mixed       $cause                The cause of the error
     *
     * @see \Symfony\Component\Translation\Translator
     */
    public function __construct(
        string $message,
        ?string $messageTemplate = null,
        array $messageParameters = [],
        ?int $messagePluralization = null,
        $cause = null
    ) {
        $this->message = $message;
        $this->messageTemplate = $messageTemplate ?? $message;
        $this->messageParameters = $messageParameters;
        $this->messagePluralization = $messagePluralization;
        $this->cause = $cause;
    }

    /**
     * Returns the error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the error message template.
     */
    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * Returns the parameters to be inserted in the message template.
     */
    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }

    /**
     * Returns the value for error message pluralization.
     */
    public function getMessagePluralization(): ?int
    {
        return $this->messagePluralization;
    }

    /**
     * Returns the cause of this error.
     *
     * @return mixed The cause of this error
     */
    public function getCause()
    {
        return $this->cause;
    }

    /**
     * Sets the node that caused this error.
     *
     * This method must only be called once.
     *
     * @param NodeInterface $origin The node that caused this error
     *
     * @throws BadMethodCallException If the method is called more than once
     */
    public function setOrigin(NodeInterface $origin): void
    {
        if (null !== $this->origin) {
            throw new BadMethodCallException('setOrigin() must only be called once.');
        }

        $this->origin = $origin;
    }

    /**
     * Returns the node that caused this error.
     *
     * @return null|NodeInterface The node that caused this error
     */
    public function getOrigin(): ?NodeInterface
    {
        return $this->origin;
    }

    /**
     * Serializes this error.
     *
     * @return string The serialized error
     */
    public function serialize(): string
    {
        return serialize([
            $this->message,
            $this->messageTemplate,
            $this->messageParameters,
            $this->messagePluralization,
            $this->cause,
        ]);
    }

    /**
     * Unserializes a serialized error.
     *
     * @param string $serialized The serialized error
     */
    public function unserialize($serialized): void
    {
        [$this->message, $this->messageTemplate, $this->messageParameters, $this->messagePluralization, $this->cause] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
