<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Provider;
use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Provider<array, mixed>
 * @psalm-immutable
 */
final class Has implements Provider
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

    /**
     * @param array $value
     * @return Validation<Failure, mixed>
     */
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
        /** @psalm-suppress InvalidArgument */
        return Constraint::of($this(...));
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
     * @return PredicateInterface<mixed>
     */
    public function asPredicate(): PredicateInterface
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
