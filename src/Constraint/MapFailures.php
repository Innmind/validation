<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant I
 * @template-covariant O
 * @implements Implementation<I, O>
 * @psalm-immutable
 */
final class MapFailures implements Implementation
{
    /**
     * @param Implementation<I, O> $constraint
     * @param \Closure(Failure): Failure $map
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
        return ($this->constraint)($value)->mapFailures($this->map);
    }

    /**
     * @internal
     * @template A
     * @template B
     * @psalm-pure
     *
     * @param Implementation<A, B> $constraint
     * @param callable(Failure): Failure $map
     *
     * @return self<A, B>
     */
    public static function of(Implementation $constraint, callable $map): self
    {
        return new self($constraint, \Closure::fromCallable($map));
    }
}
