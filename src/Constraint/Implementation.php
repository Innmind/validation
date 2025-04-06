<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant I
 * @template-covariant O
 * @psalm-immutable
 */
interface Implementation
{
    /**
     * @param I $input
     * @return Validation<Failure, O>
     */
    public function __invoke(mixed $input): Validation;

    /**
     * @template T
     *
     * @param self<O, T> $constraint
     *
     * @return self<I, T>
     */
    public function and(self $constraint): self;

    /**
     * @template T
     *
     * @param self<I, T> $constraint
     *
     * @return self<I, O|T>
     */
    public function or(self $constraint): self;

    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return self<I, T>
     */
    public function map(callable $map): self;

    /**
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate;
}
