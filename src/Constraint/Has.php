<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @implements Implementation<array, mixed>
 * @psalm-immutable
 */
final class Has implements Implementation
{
    /**
     * @param non-empty-string $key
     */
    private function __construct(
        private string $key,
    ) {
    }

    /**
     * @param array $value
     *
     * @return Validation<Failure, mixed>
     */
    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return match (\array_key_exists($this->key, $value)) {
            true => Validation::success($value[$this->key]),
            false => Validation::fail(Failure::of(
                "The key {$this->key} is missing",
            )),
        };
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function key(string $key): self
    {
        return new self($key);
    }
}
