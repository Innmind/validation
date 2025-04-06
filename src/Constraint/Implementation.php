<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

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
}
