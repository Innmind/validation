<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant A
 * @template-covariant B
 * @implements Implementation<A, B>
 * @psalm-immutable
 */
final class Of implements Implementation
{
    /**
     * @param pure-Closure(A): Validation<Failure, B> $assert
     */
    private function __construct(
        private \Closure $assert,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return ($this->assert)($value);
    }

    /**
     * @internal
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): Validation<Failure, U> $assert
     *
     * @return self<T, U>
     */
    public static function callable(callable $assert): self
    {
        return new self(\Closure::fromCallable($assert));
    }
}
