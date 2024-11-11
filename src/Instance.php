<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template-covariant T of object
 * @implements Constraint<mixed, T>
 * @psalm-immutable
 */
final class Instance implements Constraint
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
     * @param Constraint<T, V> $constraint
     *
     * @return Constraint<mixed, V>
     */
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
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
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param pure-callable(T): V $map
     *
     * @return Constraint<mixed, V>
     */
    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    /**
     * @return Predicate<T>
     */
    public function asPredicate(): Predicate
    {
        return $this->assert;
    }
}
