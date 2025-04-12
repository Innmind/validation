<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant A
 * @template-covariant B
 * @template-covariant C
 * @implements Implementation<A, B|C>
 * @psalm-immutable
 */
final class OrConstraint implements Implementation
{
    /**
     * @param Implementation<A, B> $a
     * @param Implementation<A, C> $b
     */
    private function __construct(
        private Implementation $a,
        private Implementation $b,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $input): Validation
    {
        /** @psalm-suppress MixedArgument */
        return ($this->a)($input)->otherwise(
            fn() => ($this->b)($input),
        );
    }

    /**
     * @internal
     * @template T
     * @template U
     * @template V
     * @psalm-pure
     *
     * @param Implementation<T, U> $a
     * @param Implementation<T, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Implementation $a, Implementation $b): self
    {
        return new self($a, $b);
    }
}
