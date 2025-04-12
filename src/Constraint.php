<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\{
    Constraint\Implementation,
    Constraint\Provider,
};
use Innmind\TimeContinuum\{
    Clock,
    Format,
    PointInTime as PointInTimeInterface,
};
use Innmind\Immutable\{
    Validation,
    Predicate,
    Map,
};

/**
 * @template-covariant I
 * @template-covariant O
 * @psalm-immutable
 */
final class Constraint
{
    /**
     * @param Implementation<I, O> $implementation
     */
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    /**
     * @param I $input
     *
     * @return Validation<Failure, O>
     */
    public function __invoke(mixed $input): Validation
    {
        return ($this->implementation)($input);
    }

    /**
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): Validation<Failure, U> $assert
     *
     * @return self<T, U>
     */
    public static function of(callable $assert): self
    {
        return new self(Constraint\Of::callable($assert));
    }

    /**
     * @template A of object
     * @psalm-pure
     *
     * @param class-string<A> $class
     *
     * @return self<mixed, A>
     */
    public static function instance(string $class): self
    {
        return new self(Constraint\Instance::of($class));
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, string>
     */
    public static function string(): self
    {
        return new self(Constraint\Primitive::string());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, int>
     */
    public static function int(): self
    {
        return new self(Constraint\Primitive::int());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, float>
     */
    public static function float(): self
    {
        return new self(Constraint\Primitive::float());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, array>
     */
    public static function array(): self
    {
        return new self(Constraint\Primitive::array());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, bool>
     */
    public static function bool(): self
    {
        return new self(Constraint\Primitive::bool());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, null>
     */
    public static function null(): self
    {
        return new self(Constraint\Primitive::null());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, list<mixed>>
     */
    public static function list(): self
    {
        return new self(Constraint\Primitive::list());
    }

    /**
     * @psalm-pure
     * @template K of array-key
     * @template V
     *
     * @param self<mixed, K>|Provider<mixed, K> $key
     * @param self<mixed, V>|Provider<mixed, V> $value
     *
     * @return self<mixed, Map<K, V>>
     */
    public static function associativeArray(
        self|Provider $key,
        self|Provider $value,
    ): self {
        return new self(Constraint\AssociativeArray::of(
            self::collapse($key)->implementation,
            self::collapse($value)->implementation,
        ));
    }

    /**
     * @psalm-pure
     * @template A
     *
     * @param self<mixed, A>|Provider<mixed, A> $constraint
     *
     * @return self<list, list<A>>
     */
    public static function each(self|Provider $constraint): self
    {
        return new self(Constraint\Each::of(
            self::collapse($constraint)->implementation,
        ));
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     *
     * @return self<array, mixed>
     */
    public static function hasKey(string $key): self
    {
        return new self(Constraint\Has::key($key));
    }

    /**
     * @psalm-pure
     *
     * @return self<string, PointInTimeInterface>
     */
    public static function pointInTime(Clock $clock, Format $format): self
    {
        return new self(Constraint\PointInTime::ofFormat($clock, $format));
    }

    /**
     * @psalm-pure
     * @template A
     * @template B
     *
     * @param Implementation<A, B> $implementation
     *
     * @return self<A, B>
     */
    public static function build(
        Implementation $implementation,
    ): self {
        return new self($implementation);
    }

    /**
     * @template T
     *
     * @param self<O, T>|Provider<O, T> $constraint
     *
     * @return self<I, T>
     */
    public function and(self|Provider $constraint): self
    {
        return new self(Constraint\AndConstraint::of(
            $this->implementation,
            self::collapse($constraint)->implementation,
        ));
    }

    /**
     * @template T
     *
     * @param self<I, T>|Provider<I, T> $constraint
     *
     * @return self<I, O|T>
     */
    public function or(self|Provider $constraint): self
    {
        return new self(Constraint\OrConstraint::of(
            $this->implementation,
            self::collapse($constraint)->implementation,
        ));
    }

    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return self<I, T>
     */
    public function map(callable $map): self
    {
        return new self(Constraint\Map::of(
            $this->implementation,
            $map,
        ));
    }

    /**
     * @param non-empty-string $message
     *
     * @return self<I, O>
     */
    public function failWith(string $message): self
    {
        return new self(Constraint\FailWith::of(
            $this->implementation,
            $message,
        ));
    }

    /**
     * @deprecated Use self::failWith(), this method exist for backaward compatibility
     *
     * @param non-empty-string $message
     *
     * @return self<I, O>
     */
    public function withFailure(string $message): self
    {
        return $this->failWith($message);
    }

    /**
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate
    {
        return namespace\Predicate::of($this->implementation);
    }

    /**
     * @psalm-pure
     * @template T
     * @template U
     *
     * @param self<T, U>|Provider<T, U> $constraint
     *
     * @return self<T, U>
     */
    private static function collapse(self|Provider $constraint): self
    {
        if ($constraint instanceof self) {
            return $constraint;
        }

        return $constraint->toConstraint();
    }
}
