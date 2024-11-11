<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant I
 * @template-covariant O
 * @template-covariant T
 * @implements Constraint<I, T>
 * @psalm-immutable
 */
final class Map implements Constraint
{
    /** @var Constraint<I, O> */
    private Constraint $constraint;
    /** @var pure-callable(O): T */
    private $map;

    /**
     * @param Constraint<I, O> $constraint
     * @param pure-callable(O): T $map
     */
    private function __construct(Constraint $constraint, callable $map)
    {
        $this->constraint = $constraint;
        $this->map = $map;
    }

    public function __invoke(mixed $value): Validation
    {
        return ($this->constraint)($value)->map($this->map);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @psalm-pure
     *
     * @param Constraint<A, B> $constraint
     * @param pure-callable(B): C $map
     *
     * @return self<A, B, C>
     */
    public static function of(Constraint $constraint, callable $map): self
    {
        return new self($constraint, $map);
    }

    /**
     * @template V
     *
     * @param Constraint<T, V> $constraint
     *
     * @return Constraint<I, V>
     */
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint<I, V> $constraint
     *
     * @return Constraint<I, T|V>
     */
    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param pure-callable(T): V $map
     *
     * @return self<I, T, V>
     */
    public function map(callable $map): self
    {
        return new self($this, $map);
    }

    /**
     * @return PredicateInterface<T>
     */
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
