<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant A
 * @template-covariant B
 * @template-covariant C
 * @implements Implementation<A, B|C>
 * @implements Provider<A, B|C>
 * @psalm-immutable
 */
final class OrConstraint implements Implementation, Provider
{
    /** @var Implementation<A, B>|Constraint<A, B> */
    private Implementation|Constraint $a;
    /** @var Implementation<A, C>|Constraint<A, C> */
    private Implementation|Constraint $b;

    /**
     * @param Implementation<A, B>|Constraint<A, B> $a
     * @param Implementation<A, C>|Constraint<A, C> $b
     */
    private function __construct(Implementation|Constraint $a, Implementation|Constraint $b)
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
     * @param Implementation<T, U>|Provider<T, U>|Constraint<T, U> $a
     * @param Implementation<T, V>|Provider<T, V>|Constraint<T, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Implementation|Provider|Constraint $a, Implementation|Provider|Constraint $b): self
    {
        if ($a instanceof Provider) {
            $a = $a->toConstraint();
        }

        if ($b instanceof Provider) {
            $b = $b->toConstraint();
        }

        return new self($a, $b);
    }

    /**
     * @template T
     *
     * @param Implementation<B|C, T>|Provider<B|C, T>|Constraint<B|C, T> $constraint
     *
     * @return Implementation<A, T>
     */
    #[\Override]
    public function and(Implementation|Provider|Constraint $constraint): Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Implementation<A, T>|Provider<A, T>|Constraint<A, T> $constraint
     *
     * @return self<A, B|C, T>
     */
    #[\Override]
    public function or(Implementation|Provider|Constraint $constraint): self
    {
        return self::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(B|C): T $map
     *
     * @return Implementation<A, T>
     */
    #[\Override]
    public function map(callable $map): Implementation
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
