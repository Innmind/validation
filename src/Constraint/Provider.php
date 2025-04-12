<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Constraint;

/**
 * @template-covariant T
 * @template-covariant U
 */
interface Provider
{
    /**
     * @psalm-mutation-free
     *
     * @return Constraint<T, U>
     */
    public function toConstraint(): Constraint;
}
