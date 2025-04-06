<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant A
 * @template-covariant B
 * @template-covariant C
 * @implements Constraint<A, B|C>
 * @psalm-immutable
 */
final class OrConstraint implements Constraint
{
    /** @var Constraint<A, B> */
    private Constraint $a;
    /** @var Constraint<A, C> */
    private Constraint $b;

    /**
     * @param Constraint<A, B> $a
     * @param Constraint<A, C> $b
     */
    private function __construct(Constraint $a, Constraint $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    #[\Override]
    public function __invoke(mixed $input): Validation
    {
        /** @psalm-suppress MixedArgument */
        return ($this->a)($input)->otherwise(
            fn() => ($this->b)($input),
        );
    }

    /**
     * @template T
     * @template U
     * @template V
     * @psalm-pure
     *
     * @param Constraint<T, U> $a
     * @param Constraint<T, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Constraint $a, Constraint $b): self
    {
        return new self($a, $b);
    }

    /**
     * @template T
     *
     * @param Constraint<B|C, T> $constraint
     *
     * @return Constraint<A, T>
     */
    #[\Override]
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint<A, T> $constraint
     *
     * @return self<A, B|C, T>
     */
    #[\Override]
    public function or(Constraint $constraint): self
    {
        return new self($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(B|C): T $map
     *
     * @return Constraint<A, T>
     */
    #[\Override]
    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<B|C>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
