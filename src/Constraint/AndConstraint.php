<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\{
    Map,
    OrConstraint,
    Predicate,
};
use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @internal
 * @template-covariant A
 * @template-covariant B
 * @template-covariant C
 * @implements Implementation<A, C>
 * @psalm-immutable
 */
final class AndConstraint implements Implementation
{
    /** @var Implementation<A, B> */
    private Implementation $a;
    /** @var Implementation<B, C> */
    private Implementation $b;

    /**
     * @param Implementation<A, B> $a
     * @param Implementation<B, C> $b
     */
    private function __construct(Implementation $a, Implementation $b)
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

    /**
     * @internal
     * @template T
     * @template U
     * @template V
     * @psalm-pure
     *
     * @param Implementation<T, U> $a
     * @param Implementation<U, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Implementation $a, Implementation $b): self
    {
        return new self($a, $b);
    }

    /**
     * @template T
     *
     * @param Implementation<C, T> $constraint
     *
     * @return self<A, C, T>
     */
    #[\Override]
    public function and(Implementation $constraint): self
    {
        return new self($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Implementation<A, T> $constraint
     *
     * @return Implementation<A, C|T>
     */
    #[\Override]
    public function or(Implementation $constraint): Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(C): T $map
     *
     * @return Implementation<A, T>
     */
    #[\Override]
    public function map(callable $map): Implementation
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
