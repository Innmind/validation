<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\{
    Constraint\Implementation,
    Constraint\Provider,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Validation,
    Predicate,
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
    #[\NoDiscard]
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
    #[\NoDiscard]
    public static function of(callable $assert): self
    {
        return new self(Constraint\Of::callable($assert));
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function object(): Provider\Objet
    {
        return Provider\Objet::of(self::build(...));
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function string(): Provider\Str
    {
        return Provider\Str::of(self::build(...));
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function int(): Provider\Integer
    {
        return Provider\Integer::of(self::build(...));
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, float>
     */
    #[\NoDiscard]
    public static function float(): self
    {
        return new self(Constraint\Primitive::float());
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function array(): Provider\Arr
    {
        return Provider\Arr::of(
            self::build(...),
            self::extract(...),
        );
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, bool>
     */
    #[\NoDiscard]
    public static function bool(): self
    {
        return new self(Constraint\Primitive::bool());
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, null>
     */
    #[\NoDiscard]
    public static function null(): self
    {
        return new self(Constraint\Primitive::null());
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function pointInTime(Clock $clock): Provider\Clock
    {
        return Provider\Clock::of(
            self::build(...),
            $clock,
        );
    }

    /**
     * @template T
     *
     * @param self<O, T>|Provider<O, T> $constraint
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function and(self|Provider $constraint): self
    {
        return new self(Constraint\AndConstraint::of(
            $this->implementation,
            self::collapse($constraint)->implementation,
        ));
    }

    /**
     * This will prevent the failures returned by `$constraint` from being
     * recovered when using `self::xor()`
     *
     * @template T
     *
     * @param self<O, T>|Provider<O, T> $constraint
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function guard(self|Provider $constraint): self
    {
        return new self(Constraint\Guard::of(
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
    #[\NoDiscard]
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
     * @param self<I, T>|Provider<I, T> $constraint
     *
     * @return self<I, O|T>
     */
    #[\NoDiscard]
    public function xor(self|Provider $constraint): self
    {
        return new self(Constraint\XOrConstraint::of(
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
    #[\NoDiscard]
    public function map(callable $map): self
    {
        return new self(Constraint\Map::of(
            $this->implementation,
            $map,
        ));
    }

    /**
     * @template T
     *
     * @param callable(O): self<O, T> $map
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function flatMap(callable $map): self
    {
        return new self(Constraint\FlatMap::of(
            $this->implementation,
            $map,
        ));
    }

    /**
     * @param non-empty-string $message
     *
     * @return self<I, O>
     */
    #[\NoDiscard]
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
    #[\NoDiscard]
    public function withFailure(string $message): self
    {
        return $this->failWith($message);
    }

    /**
     * @param callable(Failure): Failure $map
     *
     * @return self<I, O>
     */
    #[\NoDiscard]
    public function mapFailures(callable $map): self
    {
        return new self(Constraint\MapFailures::of(
            $this->implementation,
            $map,
        ));
    }

    /**
     * @return Predicate<O>
     */
    #[\NoDiscard]
    public function asPredicate(): Predicate
    {
        return namespace\Predicate::of($this->implementation);
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
    private static function build(
        Implementation $implementation,
    ): self {
        return new self($implementation);
    }

    /**
     * @psalm-pure
     * @template A
     * @template B
     *
     * @param self<A, B>|Provider<A, B> $constraint
     *
     * @return Implementation<A, B>
     */
    private static function extract(self|Provider $constraint): Implementation
    {
        return self::collapse($constraint)->implementation;
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
