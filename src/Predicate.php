<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Predicate as PredicateInterface;

/**
 * @internal
 * @template T
 * @implements PredicateInterface<T>
 * @psalm-immutable
 */
final class Predicate implements PredicateInterface
{
    /**
     * @param Constraint\Implementation<mixed, T> $constraint
     */
    private function __construct(
        private Constraint\Implementation $constraint,
    ) {
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
     * @internal
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
}
