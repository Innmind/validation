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
        $validation = Validation::success($value);

        /** @var mixed $element */
        foreach ($value as $element) {
            $validation = $validation->flatMap(
                fn($value) => ($this->constraint)($element)->map(
                    static fn() => $value,
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

    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
