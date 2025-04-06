<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant T of object
 * @implements Constraint\Implementation<mixed, T>
 * @psalm-immutable
 */
final class Instance implements Constraint\Implementation
{
    /** @var Predicate\Instance<T> */
    private Predicate\Instance $assert;
    /** @var class-string<T> */
    private string $class;

    /**
     * @param Predicate\Instance<T> $assert
     * @param class-string<T> $class
     */
    private function __construct(Predicate\Instance $assert, string $class)
    {
        $this->assert = $assert;
        $this->class = $class;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, T> */
        return match (($this->assert)($value)) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of("Value is not an instance of {$this->class}")),
        };
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
        return new self(Predicate\Instance::of($class), $class);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<T, V> $constraint
     *
     * @return Constraint\Implementation<mixed, V>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<mixed, V> $constraint
     *
     * @return Constraint\Implementation<mixed, T|V>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(T): V $map
     *
     * @return Constraint\Implementation<mixed, V>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return Predicate<T>
     */
    #[\Override]
    public function asPredicate(): Predicate
    {
        return $this->assert;
    }
}
