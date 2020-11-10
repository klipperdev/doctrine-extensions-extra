<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Validator\Constraints;

use Klipper\Component\DoctrineExtensionsExtra\Filterable\FilterValidator;
use Klipper\Component\Metadata\Exception\ObjectMetadataNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RequestQueryFilterValidator extends ConstraintValidator
{
    private FilterValidator $filterValidator;

    private PropertyAccessor $accessor;

    public function __construct(
        FilterValidator $filterValidator,
        ?PropertyAccessor $accessor = null
    )
    {
        $this->filterValidator = $filterValidator;
        $this->accessor = $accessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequestQueryFilter) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\RequestFilter');
        }

        if (!\is_array($value)) {
            return;
        }

        $data = $this->context->getObject();
        $metadataName = $this->accessor->getValue($data, $constraint->metadataNamePath);

        try {
            $node = $this->filterValidator->validate($metadataName, $value, $constraint->forceFirstCondition);

            if (!$node->isValid()) {
                foreach ($node->getErrors(true) as $error) {
                    $this->context->buildViolation($error->getMessage())->addViolation();
                }
            }
        } catch (ObjectMetadataNotFoundException $e) {
            $this->context->buildViolation('metadata_not_found')
                ->setInvalidValue($metadataName)
                ->setParameter('{{ value }}', $metadataName)
                ->setTranslationDomain('exceptions')
                ->addViolation()
            ;

            return;
        } catch (\Throwable $e) {
            $this->context->buildViolation($e->getMessage())->addViolation();
        }
    }
}
