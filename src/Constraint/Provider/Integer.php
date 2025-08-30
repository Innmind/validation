<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint\Provider;

use Innmind\Validation\{
    Constraint,
    Constraint\Provider,
    Constraint\Implementation,
    Constraint\Primitive,
    Constraint\Like,
    Failure,
};
use Innmind\Immutable\Validation;

/**
 * @psalm-immutable
 * @implements Provider<mixed, int>
 */
final class Integer implements Provider
{
    /** @use Like<mixed, int> */
    use Like;

    /**
     * @param pure-Closure(Implementation): Constraint $build
     */
    private function __construct(
        private \Closure $build,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param pure-Closure(Implementation): Constraint $build
     */
    #[\NoDiscard]
    public static function of(\Closure $build): self
    {
        return new self($build);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @var Constraint<mixed, int> */
        return ($this->build)(Primitive::int());
    }

    /**
     * @return Constraint<mixed, int<1, max>>
     */
    #[\NoDiscard]
    public function positive(): Constraint
    {
        return $this
            ->toConstraint()
            ->and(Constraint::of(static fn(int $int) => match (true) {
                $int <= 0 => Validation::fail(Failure::of('Integer must be above 0')),
                default => Validation::success($int),
            }));
    }

    /**
     * @return Constraint<mixed, int<min, -1>>
     */
    #[\NoDiscard]
    public function negative(): Constraint
    {
        return $this
            ->toConstraint()
            ->and(Constraint::of(static fn(int $int) => match (true) {
                $int >= 0 => Validation::fail(Failure::of('Integer must be below 0')),
                default => Validation::success($int),
            }));
    }

    /**
     * @return Constraint<mixed, int>
     */
    #[\NoDiscard]
    public function range(int $min, int $max): Constraint
    {
        return $this
            ->toConstraint()
            ->and(Constraint::of(static fn(int $int) => match (true) {
                $int < $min => Validation::fail(Failure::of("Integer cannot be lower than $min")),
                $int > $max => Validation::fail(Failure::of("Integer cannot be higher than $max")),
                default => Validation::success($int),
            }));
    }
}
