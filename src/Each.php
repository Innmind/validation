<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template T
 * @implements Constraint\Implementation<list, list<T>>
 * @implements Constraint\Provider<list, list<T>>
 * @psalm-immutable
 */
final class Each implements Constraint\Implementation, Constraint\Provider
{
    /** @var Constraint\Implementation<mixed, T> */
    private Constraint\Implementation $constraint;

    /**
     * @param Constraint\Implementation<mixed, T> $constraint
     */
    private function __construct(Constraint\Implementation $constraint)
    {
        $this->constraint = $constraint;
    }

    #[\Override]
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

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template A
     * @psalm-pure
     *
     * @param Constraint\Implementation<mixed, A> $constraint
     *
     * @return self<A>
     */
    public static function of(Constraint\Implementation $constraint): self
    {
        return new self($constraint);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<list<T>, V> $constraint
     *
     * @return Constraint\Implementation<list, V>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param Constraint\Implementation<list, V> $constraint
     *
     * @return Constraint\Implementation<list, list<T>|V>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template V
     *
     * @param callable(list<T>): V $map
     *
     * @return Constraint\Implementation<list, V>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<list<T>>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
