<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Validation;

/**
 * @template-covariant A
 * @template-covariant B
 * @implements Constraint\Implementation<A, B>
 * @implements Constraint\Provider<A, B>
 * @psalm-immutable
 */
final class Of implements Constraint\Implementation, Constraint\Provider
{
    /** @var pure-callable(A): Validation<Failure, B> */
    private $assert;

    /**
     * @param pure-callable(A): Validation<Failure, B> $assert
     */
    private function __construct(callable $assert)
    {
        $this->assert = $assert;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return ($this->assert)($value);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @template T
     * @template U
     * @psalm-pure
     *
     * @param pure-callable(T): Validation<Failure, U> $assert
     *
     * @return self<T, U>
     */
    public static function callable(callable $assert): self
    {
        return new self($assert);
    }
}
