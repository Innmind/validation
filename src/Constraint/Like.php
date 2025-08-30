<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\{
    Constraint,
    Failure,
};
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @internal
 * @psalm-immutable
 * @template-covariant I
 * @template-covariant O
 */
trait Like
{
    /**
     * @param I $input
     *
     * @return Validation<Failure, O>
     */
    public function __invoke(mixed $input): Validation
    {
        return $this->toConstraint()($input);
    }

    /**
     * @template T
     *
     * @param Constraint<O, T>|Provider<O, T> $constraint
     *
     * @return Constraint<I, T>
     */
    public function and(Constraint|Provider $constraint): Constraint
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return $this
            ->toConstraint()
            ->and($constraint);
    }

    /**
     * @template T
     *
     * @param Constraint<I, T>|Provider<I, T> $constraint
     *
     * @return Constraint<I, O|T>
     */
    public function or(Constraint|Provider $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->or($constraint);
    }

    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return Constraint<I, T>
     */
    public function map(callable $map): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @template T
     *
     * @param callable(O): Constraint<O, T> $map
     *
     * @return Constraint<I, T>
     */
    public function flatMap(callable $map): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        return $this
            ->toConstraint()
            ->flatMap($map);
    }

    /**
     * @param non-empty-string $message
     *
     * @return Constraint<I, O>
     */
    public function failWith(string $message): Constraint
    {
        return $this
            ->toConstraint()
            ->failWith($message);
    }

    /**
     * @deprecated Use self::failWith(), this method exist for backaward compatibility
     *
     * @param non-empty-string $message
     *
     * @return Constraint<I, O>
     */
    public function withFailure(string $message): Constraint
    {
        return $this->failWith($message);
    }

    /**
     * @return Predicate<O>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
