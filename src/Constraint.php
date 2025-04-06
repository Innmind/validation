<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\{
    Constraint\Implementation,
    Constraint\Provider,
};
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
