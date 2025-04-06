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
 * @implements Constraint\Implementation<A, C>
 * @implements Constraint\Provider<A, C>
 * @psalm-immutable
 */
final class AndConstraint implements Constraint\Implementation, Constraint\Provider
{
    /** @var Constraint\Implementation<A, B> */
    private Constraint\Implementation $a;
    /** @var Constraint\Implementation<B, C> */
    private Constraint\Implementation $b;

    /**
     * @param Constraint\Implementation<A, B> $a
     * @param Constraint\Implementation<B, C> $b
     */
    private function __construct(Constraint\Implementation $a, Constraint\Implementation $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    #[\Override]
    public function __invoke(mixed $input): Validation
    {
        /** @psalm-suppress MixedArgument */
        return ($this->a)($input)->flatMap(
            fn($value) => ($this->b)($value),
        );
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template T
     * @template U
     * @template V
     * @psalm-pure
     *
     * @param Constraint\Implementation<T, U> $a
     * @param Constraint\Implementation<U, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Constraint\Implementation $a, Constraint\Implementation $b): self
    {
        return new self($a, $b);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<C, T> $constraint
     *
     * @return self<A, C, T>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): self
    {
        return new self($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<A, T> $constraint
     *
     * @return Constraint\Implementation<A, C|T>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(C): T $map
     *
     * @return Constraint\Implementation<A, T>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<C>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
