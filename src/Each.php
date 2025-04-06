<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @template T
 * @implements Provider<list, list<T>>
 * @psalm-immutable
 */
final class Each implements Provider
{
    /** @var Implementation<mixed, T>|Constraint<mixed, T> */
    private Implementation|Constraint $constraint;

    /**
     * @param Implementation<mixed, T>|Constraint<mixed, T> $constraint
     */
    private function __construct(Implementation|Constraint $constraint)
    {
        $this->constraint = $constraint;
    }

    /**
     * @param list $value
     *
     * @return Validation<Failure, list<T>>
     */
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
        /** @psalm-suppress InvalidArgument */
        return Constraint::of($this(...));
    }

    /**
     * @template A
     * @psalm-pure
     *
     * @param Implementation<mixed, A>|Provider<mixed, A>|Constraint<mixed, A> $constraint
     *
     * @return self<A>
     */
    public static function of(Implementation|Provider|Constraint $constraint): self
    {
        if ($constraint instanceof Provider) {
            $constraint = $constraint->toConstraint();
        }

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
        return $this
            ->toConstraint()
            ->and($constraint);
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
        return $this
            ->toConstraint()
            ->or($constraint);
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
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @return Predicate<list<T>>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
