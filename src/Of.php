<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Validation;

/**
 * @psalm-immutable
 */
final class Of
{
    private function __construct()
    {
    }

    /**
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): Validation<Failure, U> $assert
     *
     * @return Constraint<T, U>
     */
    public static function callable(callable $assert): Constraint
    {
        return Constraint::of($assert);
    }
}
