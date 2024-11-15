<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template T
 * @implements Constraint<list, list<T>>
 * @psalm-immutable
 */
final class Each implements Constraint
{
    /** @var Constraint<mixed, T> */
    private Constraint $constraint;

    /**
     * @param Constraint<mixed, T> $constraint
     */
    private function __construct(Constraint $constraint)
    {
        $this->constraint = $constraint;
    }

    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, list<T>> */
        $validation = Validation::success([]);

        /** @var mixed $element */
        foreach ($value as $element) {
            $validation = $validation->flatMap(
                fn($carry) => ($this->constraint)($element)->map(
                    static fn($value) => \array_merge($carry, [$value]),
                ),
            );
        }

        return $validation;
    }

    /**
     * @template A
     * @psalm-pure
     *
     * @param Constraint<mixed, A> $constraint
     *
     * @return self<A>
     */
    public static function of(Constraint $constraint): self
    {
        return new self($constraint);
    }

    /**
     * @template V
     *
     * @param Constraint<list<T>, V> $constraint
     *
     * @return Constraint<list, V>
     */
    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint<list, V> $constraint
     *
     * @return Constraint<list, list<T>|V>
     */
    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(list<T>): V $map
     *
     * @return Constraint<list, V>
     */
    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<list<T>>
     */
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
