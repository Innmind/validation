<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Provider;

/**
 * @psalm-immutable
 */
final class Each
{
    private function __construct()
    {
    }

    /**
     * @template A
     * @psalm-pure
     *
     * @param Provider<mixed, A>|Constraint<mixed, A> $constraint
     *
     * @return Constraint<list, list<A>>
     */
    public static function of(Provider|Constraint $constraint): Constraint
    {
        return Constraint::each($constraint);
    }
}
