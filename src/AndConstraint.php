<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\Validation;

/**
 * @template-covariant A
 * @template-covariant B
 * @template-covariant C
 * @implements Implementation<A, C>
 * @implements Provider<A, C>
 * @psalm-immutable
 */
final class AndConstraint implements Implementation, Provider
{
    /** @var Implementation<A, B>|Constraint<A, B> */
    private Implementation|Constraint $a;
    /** @var Implementation<B, C>|Constraint<B, C> */
    private Implementation|Constraint $b;

    /**
     * @param Implementation<A, B>|Constraint<A, B> $a
     * @param Implementation<B, C>|Constraint<B, C> $b
     */
    private function __construct(Implementation|Constraint $a, Implementation|Constraint $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    #[\Override]
    public function __invoke(mixed $input): Validation
    {
        /** @psalm-suppress MixedArgument */
        return ($this->a)($input)->flatMap(
            fn($value) => ($this->b)($value),
        );
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template T
     * @template U
     * @template V
     * @psalm-pure
     *
     * @param Implementation<T, U>|Provider<T, U>|Constraint<T, U> $a
     * @param Implementation<U, V>|Provider<U, V>|Constraint<U, V> $b
     *
     * @return self<T, U, V>
     */
    public static function of(Implementation|Provider|Constraint $a, Implementation|Provider|Constraint $b): self
    {
        if ($a instanceof Provider) {
            $a = $a->toConstraint();
        }

        if ($b instanceof Provider) {
            $b = $b->toConstraint();
        }

        return new self($a, $b);
    }
}
