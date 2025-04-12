<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint\Provider;

use Innmind\Validation\{
    Constraint,
    Constraint\Provider,
    Constraint\Implementation,
    Constraint\Primitive,
    Constraint\Instance,
    Constraint\Like,
};

/**
 * @psalm-immutable
 * @implements Provider<mixed, object>
 */
final class Objet implements Provider
{
    /** @use Like<mixed, object> */
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
        /** @var Constraint<mixed, object> */
        return ($this->build)(Primitive::object());
    }

    /**
     * @template A of object
     *
     * @param class-string<A> $class
     *
     * @return Constraint<mixed, A>
     */
    public function instance(string $class): Constraint
    {
        /** @var Constraint<mixed, A> */
        return ($this->build)(Instance::of($class));
    }
}
