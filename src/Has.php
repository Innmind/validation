<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Constraint<array, mixed>
 * @psalm-immutable
 */
final class Has implements Constraint
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

    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
