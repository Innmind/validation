<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Implementation;
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant I
 * @template-covariant O
 * @psalm-immutable
 */
final class Constraint
{
    /**
     * @param Implementation<I, O> $implementation
     */
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    /**
     * @param I $input
     * @return Validation<Failure, O>
     */
    public function __invoke(mixed $input): Validation
    {
        return ($this->implementation)($input);
    }

    /**
     * @psalm-pure
     * @template A
     * @template B
     *
     * @param Implementation<A, B> $implementation
     *
     * @return self<A, B>
     */
    public static function of(
        Implementation $implementation,
    ): self {
        return new self($implementation);
    }

    /**
     * @template T
     *
     * @param self<O, T> $constraint
     *
     * @return self<I, T>
     */
    public function and(self $constraint): self
    {
        return new self(AndConstraint::of(
            $this->implementation,
            $constraint->implementation,
        ));
    }

    /**
     * @template T
     *
     * @param self<I, T> $constraint
     *
     * @return self<I, O|T>
     */
    public function or(self $constraint): self
    {
        return new self(OrConstraint::of(
            $this->implementation,
            $constraint->implementation,
        ));
    }

    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return self<I, T>
     */
    public function map(callable $map): self
    {
        return new self(Map::of(
            $this->implementation,
            $map,
        ));
    }

    /**
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate
    {
        return namespace\Predicate::of($this->implementation);
    }
}
