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
    public function __invoke($input): Validation;

    /**
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate;
}
