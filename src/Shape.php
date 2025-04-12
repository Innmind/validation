<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Provider,
    Provider\Arr,
};

/**
 * @psalm-immutable
 */
final class Shape
{
    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function of(string $key, Provider|Constraint $constraint): Arr\Shape
    {
        return Constraint::array()->shape($key, $constraint);
    }
}
