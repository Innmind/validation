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
 * @implements Constraint\Implementation<I, T>
 * @psalm-immutable
 */
final class Map implements Constraint\Implementation
{
    /** @var Constraint\Implementation<I, O> */
    private Constraint\Implementation $constraint;
    /** @var callable(O): T */
    private $map;

    /**
     * @param Constraint\Implementation<I, O> $constraint
     * @param callable(O): T $map
     */
    private function __construct(Constraint\Implementation $constraint, callable $map)
    {
        $this->constraint = $constraint;
        $this->map = $map;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @psalm-suppress ImpureFunctionCall */
        return ($this->constraint)($value)->map($this->map);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @psalm-pure
     *
     * @param Constraint\Implementation<A, B> $constraint
     * @param callable(B): C $map
     *
     * @return self<A, B, C>
     */
    public static function of(Constraint\Implementation $constraint, callable $map): self
    {
        return new self($constraint, $map);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<T, V> $constraint
     *
     * @return Constraint\Implementation<I, V>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<I, V> $constraint
     *
     * @return Constraint\Implementation<I, T|V>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(T): V $map
     *
     * @return self<I, T, V>
     */
    #[\Override]
    public function map(callable $map): self
    {
        return new self($this, $map);
    }

    /**
     * @return PredicateInterface<T>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
