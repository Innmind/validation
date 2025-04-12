<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant I
 * @template-covariant O
 * @template-covariant T
 * @implements Implementation<I, T>
 * @psalm-immutable
 */
final class Map implements Implementation
{
    /**
     * @param Implementation<I, O> $constraint
     * @param \Closure(O): T $map
     */
    private function __construct(
        private Implementation $constraint,
        private \Closure $map,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @psalm-suppress ImpureFunctionCall */
        return ($this->constraint)($value)->map($this->map);
    }

    /**
     * @internal
     * @template A
     * @template B
     * @template C
     * @psalm-pure
     *
     * @param Implementation<A, B> $constraint
     * @param callable(B): C $map
     *
     * @return self<A, B, C>
     */
    public static function of(Implementation $constraint, callable $map): self
    {
        return new self($constraint, \Closure::fromCallable($map));
    }
}
