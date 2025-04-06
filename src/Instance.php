<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant T of object
 * @implements Constraint\Provider<mixed, T>
 * @psalm-immutable
 */
final class Instance implements Constraint\Provider
{
    /** @var class-string<T> */
    private string $class;

    /**
     * @param class-string<T> $class
     */
    private function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @return Validation<Failure, T>
     */
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, T> */
        return match ($value instanceof $this->class) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of("Value is not an instance of {$this->class}")),
        };
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        return Constraint::of($this(...));
    }

    /**
     * @template A of object
     * @psalm-pure
     *
     * @param class-string<A> $class
     *
     * @return self<A>
     */
    public static function of(string $class): self
    {
        return new self($class);
    }

    /**
     * @template V
     *
     * @param Constraint<T, V> $constraint
     *
     * @return Constraint<mixed, V>
     */
    public function and(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->and($constraint);
    }

    /**
     * @template V
     *
     * @param Constraint<mixed, V> $constraint
     *
     * @return Constraint<mixed, T|V>
     */
    public function or(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->or($constraint);
    }

    /**
     * @template V
     *
     * @param callable(T): V $map
     *
     * @return Constraint<mixed, V>
     */
    public function map(callable $map): Constraint
    {
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @return Predicate<T>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
