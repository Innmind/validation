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
 * @implements Constraint<T, U>
 * @psalm-immutable
 */
final class Is implements Constraint
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
     * @param Constraint<mixed, E> $each
     *
     * @return Constraint<mixed, list<E>>
     */
    public static function list(?Constraint $each = null): Constraint
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
    public static function shape(string $key, Constraint $constraint): Shape
    {
        return Shape::of($key, $constraint);
    }

    /**
     * @psalm-pure
     * @template K
     * @template V
     *
     * @param Constraint<mixed, K> $key
     * @param Constraint<mixed, V> $value
     *
     * @return AssociativeArray<K, V>
     */
    public static function associativeArray(Constraint $key, Constraint $value): AssociativeArray
    {
        return AssociativeArray::of($key, $value);
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param ?non-empty-string $message
     *
     * @return Constraint<Maybe<V>, V>
     */
    public static function just(?string $message = null): Constraint
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
     * @param Constraint<U, V> $constraint
     *
     * @return Constraint<T, V>
     */
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint<T, V> $constraint
     *
     * @return Constraint<T, U|V>
     */
    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(U): V $map
     *
     * @return Constraint<T, V>
     */
    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<U>
     */
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
