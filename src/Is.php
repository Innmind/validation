<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Maybe,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant T
 * @template-covariant U
 * @implements Constraint\Implementation<T, U>
 * @psalm-immutable
 */
final class Is implements Constraint\Implementation
{
    /** @var pure-callable(T): bool */
    private $assert;
    /** @var non-empty-string */
    private string $type;
    /** @var ?non-empty-string */
    private ?string $message;

    /**
     * @param pure-callable(T): bool $assert
     * @param non-empty-string $type
     * @param ?non-empty-string $message
     */
    private function __construct(
        callable $assert,
        string $type,
        ?string $message = null,
    ) {
        $this->assert = $assert;
        $this->type = $type;
        $this->message = $message;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, U> */
        return match (($this->assert)($value)) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of(
                $this->message ?? "Value is not of type {$this->type}",
            )),
        };
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, string>
     */
    public static function string(): self
    {
        /** @var self<mixed, string> */
        return new self(\is_string(...), 'string');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, int>
     */
    public static function int(): self
    {
        /** @var self<mixed, int> */
        return new self(\is_int(...), 'int');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, float>
     */
    public static function float(): self
    {
        /** @var self<mixed, float> */
        return new self(\is_float(...), 'float');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, array>
     */
    public static function array(): self
    {
        /** @var self<mixed, array> */
        return new self(\is_array(...), 'array');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, bool>
     */
    public static function bool(): self
    {
        /** @var self<mixed, bool> */
        return new self(\is_bool(...), 'bool');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, null>
     */
    public static function null(): self
    {
        /** @var self<mixed, null> */
        return new self(\is_null(...), 'null');
    }

    /**
     * @psalm-pure
     *
     * @template E
     *
     * @param Constraint\Implementation<mixed, E> $each
     *
     * @return Constraint\Implementation<mixed, list<E>>
     */
    public static function list(?Constraint\Implementation $each = null): Constraint\Implementation
    {
        /** @var self<array, list<mixed>> */
        $list = new self(\array_is_list(...), 'list');

        $constraint = self::array()->and($list);

        return match ($each) {
            null => $constraint,
            default => $constraint->and(Each::of($each)),
        };
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function shape(string $key, Constraint\Implementation $constraint): Shape
    {
        return Shape::of($key, $constraint);
    }

    /**
     * @psalm-pure
     * @template K
     * @template V
     *
     * @param Constraint\Implementation<mixed, K> $key
     * @param Constraint\Implementation<mixed, V> $value
     *
     * @return AssociativeArray<K, V>
     */
    public static function associativeArray(Constraint\Implementation $key, Constraint\Implementation $value): AssociativeArray
    {
        return AssociativeArray::of($key, $value);
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param ?non-empty-string $message
     *
     * @return Constraint\Implementation<Maybe<V>, V>
     */
    public static function just(?string $message = null): Constraint\Implementation
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return Of::callable(static fn(Maybe $value) => $value->match(
            Validation::success(...),
            static fn() => Validation::fail(Failure::of(
                $message ?? 'No value was provided',
            )),
        ));
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param V $value
     * @param ?non-empty-string $message
     *
     * @return Constraint\Implementation<mixed, V>
     */
    public static function value(mixed $value, ?string $message = null): Constraint\Implementation
    {
        return Of::callable(static fn(mixed $in) => match ($in) {
            $value => Validation::success($value),
            default => Validation::fail(Failure::of(
                $message ?? \sprintf(
                    'Not of expected value of type %s',
                    \gettype($value),
                ),
            )),
        });
    }

    /**
     * @param non-empty-string $message
     *
     * @return self<T, U>
     */
    public function withFailure(string $message): self
    {
        return new self($this->assert, $this->type, $message);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<U, V> $constraint
     *
     * @return Constraint\Implementation<T, V>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<T, V> $constraint
     *
     * @return Constraint\Implementation<T, U|V>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(U): V $map
     *
     * @return Constraint\Implementation<T, V>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<U>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
