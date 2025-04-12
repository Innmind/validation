<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant T of object
 * @implements Implementation<mixed, T>
 * @psalm-immutable
 */
final class Instance implements Implementation
{
    /**
     * @param class-string<T> $class
     */
    private function __construct(
        private string $class,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, T> */
        return match ($value instanceof $this->class) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of("Value is not an instance of {$this->class}")),
        };
    }

    /**
     * @internal
     * @template A of object
     * @psalm-pure
     *
     * @param class-string<A> $class
     *
     * @return self<A>
     */
    public static function of(string $class): self
    {
        return new self($class);
    }
}
