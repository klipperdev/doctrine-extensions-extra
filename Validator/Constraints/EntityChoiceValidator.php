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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtensionsExtra\Entity\Repository\Traits\TranslatableRepositoryInterface;
use Klipper\Component\DoctrineExtensionsExtra\Util\IdReader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class EntityChoiceValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * Constructor.
     *
     * @param ManagerRegistry       $registry The doctrine registry
     * @param null|PropertyAccessor $accessor The property accessor
     */
    public function __construct(ManagerRegistry $registry, ?PropertyAccessor $accessor = null)
    {
        $this->registry = $registry;
        $this->accessor = $accessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EntityChoice) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\EntityChoice');
        }

        if (null !== $constraint->choices) {
            throw new ConstraintDefinitionException('The "choices" must be null');
        }

        if (null !== $constraint->callback) {
            throw new ConstraintDefinitionException('The "callback" must be null');
        }

        if (true !== $constraint->strict) {
            throw new ConstraintDefinitionException('The "strict" must be true');
        }

        if (null === $value) {
            return;
        }

        if ($constraint->multiple) {
            if (!\is_array($value)) {
                throw new UnexpectedValueException($value, 'array');
            }

            if (empty($value)) {
                return;
            }
        }

        if ($constraint->em) {
            $em = $this->registry->getManager($constraint->em);

            if (null === $em) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->em));
            }
        } else {
            $em = $this->registry->getManagerForClass($constraint->entityClass);

            if (null === $em) {
                throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', $constraint->entityClass));
            }
        }

        /* @var EntityChoice $constraint */
        $this->doValidate($em, $constraint, $value);
    }

    /**
     * @param null|string|string[] $value
     */
    private function doValidate(ObjectManager $om, EntityChoice $constraint, $value): void
    {
        $repo = $om->getRepository($constraint->entityClass);
        $repoMethod = $constraint->repositoryMethod;
        $repoMethod = $repo instanceof TranslatableRepositoryInterface
            && 'findBy' === $repoMethod ? 'findTranslatedBy' : $repoMethod;

        if ($constraint->multiple) {
            $count = \count($value);

            if (null !== $constraint->min && $count < $constraint->min) {
                $this->context->buildViolation($constraint->minMessage)
                    ->setParameter('{{ limit }}', $constraint->min)
                    ->setPlural((int) $constraint->min)
                    ->setCode(Choice::TOO_FEW_ERROR)
                    ->addViolation()
                ;

                return;
            }

            if (null !== $constraint->max && $count > $constraint->max) {
                $this->context->buildViolation($constraint->maxMessage)
                    ->setParameter('{{ limit }}', $constraint->max)
                    ->setPlural((int) $constraint->max)
                    ->setCode(Choice::TOO_MANY_ERROR)
                    ->addViolation()
                ;

                return;
            }
        }

        $idReader = new IdReader($om, $om->getClassMetadata($constraint->entityClass));
        $filters = SqlFilterUtil::findFilters($om, (array) $constraint->filters, $constraint->allFilters);
        $namePath = $constraint->namePath ?? $idReader->getIdField();

        SqlFilterUtil::disableFilters($om, $filters);
        $result = $repo->{$repoMethod}([
            $namePath => (array) $value,
        ]);
        SqlFilterUtil::enableFilters($om, $filters);

        $result = array_map(function ($value) use ($namePath) {
            if (!\is_object($value)) {
                return $value;
            }

            return $this->accessor->getValue($value, $namePath);
        }, $result);

        $invalidValues = array_diff((array) $value, $result);

        if (($count = \count($invalidValues)) > 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($invalidValues))
                ->setPlural($count)
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->addViolation()
            ;
        }
    }
}
