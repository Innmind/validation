<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Predicate as PredicateInterface;

/**
 * @template T
 * @implements PredicateInterface<T>
 * @psalm-immutable
 */
final class Predicate implements PredicateInterface
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

    #[\Override]
    public function __invoke(mixed $value): bool
    {
        return ($this->constraint)($value)->match(
            static fn() => true,
            static fn() => false,
        );
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
}
