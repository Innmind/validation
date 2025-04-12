<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint\Provider;

use Innmind\Validation\{
    Constraint,
    Constraint\Provider,
    Constraint\Primitive,
    Constraint\Implementation,
    Constraint\Like,
    Constraint\AndConstraint,
    Constraint\AssociativeArray,
    Constraint\Has,
    Constraint\Each,
};
use Innmind\Immutable\Map;

/**
 * @psalm-immutable
 * @implements Provider<mixed, array>
 */
final class Arr implements Provider
{
    /** @use Like<mixed, array> */
    use Like;

    /**
     * @param pure-Closure(Implementation): Constraint $build
     * @param pure-Closure(Constraint|Provider): Implementation $extract
     */
    private function __construct(
        private \Closure $build,
        private \Closure $extract,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param pure-Closure(Implementation): Constraint $build
     * @param pure-Closure(Constraint|Provider): Implementation $extract
     */
    public static function of(\Closure $build, \Closure $extract): self
    {
        return new self($build, $extract);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @var Constraint<mixed, array> */
        return ($this->build)(Primitive::array());
    }

    /**
     * @template E
     *
     * @param Constraint<mixed, E>|Provider<mixed, E>|null $each
     *
     * @return Constraint<mixed, list<E>>
     */
    public function list(Constraint|Provider|null $each = null): Constraint
    {
        $constraint = AndConstraint::of(
            Primitive::array(),
            Primitive::list(),
        );

        $constraint = match ($each) {
            null => $constraint,
            default => AndConstraint::of(
                $constraint,
                Each::of(($this->extract)($each)),
            ),
        };

        /** @var Constraint<mixed, list<E>> */
        return ($this->build)($constraint);
    }

    /**
     * @template K of array-key
     * @template V
     *
     * @param Constraint<mixed, K>|Provider<mixed, K> $key
     * @param Constraint<mixed, V>|Provider<mixed, V> $value
     *
     * @return Constraint<mixed, Map<K, V>>
     */
    public function associative(
        Constraint|Provider $key,
        Constraint|Provider $value,
    ): Constraint {
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Constraint<mixed, Map<K, V>>
         */
        return ($this->build)(AssociativeArray::of(
            ($this->extract)($key),
            ($this->extract)($value),
        ));
    }

    /**
     * The returned value on success is the key value and not the whole array.
     *
     * @param non-empty-string $key
     *
     * @return Constraint<mixed, mixed>
     */
    public function hasKey(string $key): Constraint
    {
        /** @var Constraint<mixed, mixed> */
        return ($this->build)(AndConstraint::of(
            Primitive::array(),
            Has::key($key),
        ));
    }

    /**
     * @param non-empty-string $key
     */
    public function shape(string $key, Provider|Constraint $constraint): Arr\Shape
    {
        return Arr\Shape::of(
            $this->build,
            $key,
            $constraint,
        );
    }
}
