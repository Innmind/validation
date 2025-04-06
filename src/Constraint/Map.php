<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant I
 * @template-covariant O
 * @template-covariant T
 * @implements Implementation<I, T>
 * @psalm-immutable
 */
final class Map implements Implementation
{
    /** @var Implementation<I, O> */
    private Implementation $constraint;
    /** @var callable(O): T */
    private $map;

    /**
     * @param Implementation<I, O> $constraint
     * @param callable(O): T $map
     */
    private function __construct(Implementation $constraint, callable $map)
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

    /**
     * @internal
     * @template A
     * @template B
     * @template C
     * @psalm-pure
     *
     * @param Implementation<A, B> $constraint
     * @param callable(B): C $map
     *
     * @return self<A, B, C>
     */
    public static function of(Implementation $constraint, callable $map): self
    {
        return new self($constraint, $map);
    }
}
