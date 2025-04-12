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
 * @implements Provider<mixed, string>
 */
final class Str implements Provider
{
    /** @use Like<mixed, string> */
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
    public static function of(\Closure $build): self
    {
        return new self($build);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @var Constraint<mixed, string> */
        return ($this->build)(Primitive::string());
    }

    /**
     * @return Constraint<mixed, non-empty-string>
     */
    public function nonEmpty(): Constraint
    {
        return $this
            ->toConstraint()
            ->and(Constraint::of(static fn(string $string) => match ($string) {
                '' => Validation::fail(Failure::of('String cannot be empty')),
                default => Validation::success($string),
            }));
    }
}
