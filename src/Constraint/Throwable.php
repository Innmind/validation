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
final class Throwable implements Implementation
{
    /**
     * @param pure-Closure(A): B $assert
     */
    private function __construct(
        private \Closure $assert,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        try {
            return Validation::success(($this->assert)($value));
        } catch (\Throwable $e) {
            return Validation::fail(Failure::of(
                \sprintf(
                    'Callable has thrown %s(%s)',
                    $e::class,
                    $e->getMessage(),
                ),
            ));
        }
    }

    /**
     * @internal
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): U $assert
     *
     * @return self<T, U>
     */
    public static function of(callable $assert): self
    {
        return new self(\Closure::fromCallable($assert));
    }
}
