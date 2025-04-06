<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Validation;

/**
 * @template-covariant I
 * @template-covariant O
 * @template-covariant T
 * @implements Constraint\Implementation<I, T>
 * @implements Constraint\Provider<I, T>
 * @psalm-immutable
 */
final class Map implements Constraint\Implementation, Constraint\Provider
{
    /** @var Constraint\Implementation<I, O> */
    private Constraint\Implementation $constraint;
    /** @var callable(O): T */
    private $map;

    /**
     * @param Constraint\Implementation<I, O> $constraint
     * @param callable(O): T $map
     */
    private function __construct(Constraint\Implementation $constraint, callable $map)
    {
        $this->constraint = $constraint;
        $this->map = $map;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @psalm-suppress ImpureFunctionCall */
        return ($this->constraint)($value)->map($this->map);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @psalm-pure
     *
     * @param Constraint\Implementation<A, B> $constraint
     * @param callable(B): C $map
     *
     * @return self<A, B, C>
     */
    public static function of(Constraint\Implementation $constraint, callable $map): self
    {
        return new self($constraint, $map);
    }
}
