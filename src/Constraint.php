<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant I
 * @template-covariant O
 * @psalm-immutable
 */
interface Constraint
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
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate;
}
