<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Constraint\Implementation<array, mixed>
 * @implements Constraint\Provider<array, mixed>
 * @psalm-immutable
 */
final class Has implements Constraint\Implementation, Constraint\Provider
{
    /** @var non-empty-string */
    private string $key;
    /** @var callable(non-empty-string): non-empty-string */
    private $message;

    /**
     * @param non-empty-string $key
     * @param callable(non-empty-string): non-empty-string $message
     */
    private function __construct(string $key, callable $message)
    {
        $this->key = $key;
        $this->message = $message;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @psalm-suppress ImpureFunctionCall */
        return match (\array_key_exists($this->key, $value)) {
            true => Validation::success($value[$this->key]),
            false => Validation::fail(Failure::of(
                ($this->message)($this->key),
            )),
        };
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
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
        return new self(
            $key,
            static fn($key) => "The key $key is missing",
        );
    }

    /**
     * @param callable(non-empty-string): non-empty-string $message The input is the key
     */
    public function withFailure(callable $message): self
    {
        return new self($this->key, $message);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<mixed, T> $constraint
     *
     * @return Constraint\Implementation<array, T>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<array, T> $constraint
     *
     * @return Constraint\Implementation<array, mixed|T>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(mixed): T $map
     *
     * @return Constraint\Implementation<array, T>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<mixed>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
