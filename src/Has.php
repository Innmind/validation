<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Provider,
    Like,
};

/**
 * @implements Provider<mixed, mixed>
 * @psalm-immutable
 */
final class Has implements Provider
{
    /** @use Like<mixed, mixed> */
    use Like;

    /**
     * @param non-empty-string $key
     */
    private function __construct(private string $key)
    {
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::array()->hasKey($this->key);
    }

    /**
     * The returned value on success is the key value and not the whole array.
     *
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function key(string $key): self
    {
        return new self($key);
    }

    /**
     * @deprecated
     *
     * @param callable(non-empty-string): non-empty-string $message The input is the key
     *
     * @return Constraint<mixed, mixed>
     */
    public function withFailure(callable $message): Constraint
    {
        /** @psalm-suppress ImpureFunctionCall */
        return $this
            ->toConstraint()
            ->failWith($message($this->key));
    }
}
