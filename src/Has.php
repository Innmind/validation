<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Provider;
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @implements Provider<array, mixed>
 * @psalm-immutable
 */
final class Has implements Provider
{
    /**
     * @param non-empty-string $key
     */
    private function __construct(private string $key)
    {
    }

    /**
     * @deprecated
     *
     * @param array $value
     *
     * @return Validation<Failure, mixed>
     */
    public function __invoke(mixed $value): Validation
    {
        return $this->toConstraint()($value);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::hasKey($this->key);
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
     * @param callable(non-empty-string): non-empty-string $message The input is the key
     *
     * @return Constraint<array, mixed>
     */
    public function withFailure(callable $message): Constraint
    {
        /** @psalm-suppress ImpureFunctionCall */
        return $this
            ->toConstraint()
            ->failWith($message($this->key));
    }

    /**
     * @template T
     *
     * @param Constraint<mixed, T> $constraint
     *
     * @return Constraint<array, T>
     */
    public function and(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->and($constraint);
    }

    /**
     * @template T
     *
     * @param Constraint<array, T> $constraint
     *
     * @return Constraint<array, mixed|T>
     */
    public function or(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->or($constraint);
    }

    /**
     * @template T
     *
     * @param callable(mixed): T $map
     *
     * @return Constraint<array, T>
     */
    public function map(callable $map): Constraint
    {
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @return Predicate<mixed>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
