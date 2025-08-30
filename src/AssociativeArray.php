<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Provider;
use Innmind\Immutable\Map;

/**
 * @psalm-immutable
 */
final class AssociativeArray
{
    private function __construct(
    ) {
    }

    /**
     * @psalm-pure
     * @template A of array-key
     * @template B
     *
     * @param Provider<mixed, A>|Constraint<mixed, A> $key
     * @param Provider<mixed, B>|Constraint<mixed, B> $value
     *
     * @return Constraint<mixed, Map<A, B>>
     */
    #[\NoDiscard]
    public static function of(Provider|Constraint $key, Provider|Constraint $value): Constraint
    {
        return Constraint::array()->associative($key, $value);
    }
}
