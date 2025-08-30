<?php
declare(strict_types = 1);

namespace Innmind\Validation;

/**
 * @psalm-immutable
 */
final class Instance
{
    private function __construct()
    {
    }

    /**
     * @template A of object
     * @psalm-pure
     *
     * @param class-string<A> $class
     *
     * @return Constraint<mixed, A>
     */
    #[\NoDiscard]
    public static function of(string $class): Constraint
    {
        return Constraint::object()->instance($class);
    }
}
