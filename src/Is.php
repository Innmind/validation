<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Maybe,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant T
 * @template-covariant U
 * @implements Implementation<T, U>
 * @implements Provider<T, U>
 * @psalm-immutable
 */
final class Is implements Implementation, Provider
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

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
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
     * @param Implementation<mixed, E> $each
     *
     * @return Implementation<mixed, list<E>>
     */
    public static function list(?Implementation $each = null): Implementation
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
    public static function shape(string $key, Implementation $constraint): Shape
    {
        return Shape::of($key, $constraint);
    }

    /**
     * @psalm-pure
     * @template K
     * @template V
     *
     * @param Implementation<mixed, K> $key
     * @param Implementation<mixed, V> $value
     *
     * @return AssociativeArray<K, V>
     */
    public static function associativeArray(Implementation $key, Implementation $value): AssociativeArray
    {
        return AssociativeArray::of($key, $value);
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param ?non-empty-string $message
     *
     * @return Implementation<Maybe<V>, V>
     */
    public static function just(?string $message = null): Implementation
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
     * @return Implementation<mixed, V>
     */
    public static function value(mixed $value, ?string $message = null): Implementation
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
     * @param Implementation<U, V>|Provider<U, V>|Constraint<U, V> $constraint
     *
     * @return AndConstraint<T, U, V>
     */
    #[\Override]
    public function and(Implementation|Provider|Constraint $constraint): AndConstraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Implementation<T, V>|Provider<T, V>|Constraint<T, V> $constraint
     *
     * @return OrConstraint<T, U, V>
     */
    #[\Override]
    public function or(Implementation|Provider|Constraint $constraint): OrConstraint
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(U): V $map
     *
     * @return Implementation<T, V>
     */
    #[\Override]
    public function map(callable $map): Implementation
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
