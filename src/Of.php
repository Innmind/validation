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
 * @implements Constraint<A, B>
 * @psalm-immutable
 */
final class Of implements Constraint
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

    public function __invoke(mixed $value): Validation
    {
        return ($this->assert)($value);
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
     * @param Constraint<B, T> $constraint
     *
     * @return Constraint<A, T>
     */
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint<A, T> $constraint
     *
     * @return Constraint<A, B|T>
     */
    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(B): T $map
     *
     * @return Constraint<A, T>
     */
    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<B>
     */
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
