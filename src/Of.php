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
 * @implements Constraint\Implementation<A, B>
 * @implements Constraint\Provider<A, B>
 * @psalm-immutable
 */
final class Of implements Constraint\Implementation, Constraint\Provider
{
    /** @var pure-callable(A): Validation<Failure, B> */
    private $assert;

    /**
     * @param pure-callable(A): Validation<Failure, B> $assert
     */
    private function __construct(callable $assert)
    {
        $this->assert = $assert;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return ($this->assert)($value);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): Validation<Failure, U> $assert
     *
     * @return self<T, U>
     */
    public static function callable(callable $assert): self
    {
        return new self($assert);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<B, T> $constraint
     *
     * @return Constraint\Implementation<A, T>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<A, T> $constraint
     *
     * @return Constraint\Implementation<A, B|T>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(B): T $map
     *
     * @return Constraint\Implementation<A, T>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<B>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
