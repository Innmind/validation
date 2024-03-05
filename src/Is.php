<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant T
 * @template-covariant U
 * @implements Constraint<T, U>
 * @psalm-immutable
 */
final class Is implements Constraint
{
    /** @var pure-callable(T): bool */
    private $assert;
    /** @var non-empty-string */
    private string $type;

    /**
     * @param pure-callable(T): bool $assert
     * @param non-empty-string $type
     */
    private function __construct(callable $assert, string $type)
    {
        $this->assert = $assert;
        $this->type = $type;
    }

    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, U> */
        return match (($this->assert)($value)) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of("Value is not of type {$this->type}")),
        };
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, string>
     */
    public static function string(): self
    {
        /** @var self<mixed, string> */
        return new self(\is_string(...), 'string');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, int>
     */
    public static function int(): self
    {
        /** @var self<mixed, int> */
        return new self(\is_int(...), 'int');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, float>
     */
    public static function float(): self
    {
        /** @var self<mixed, float> */
        return new self(\is_float(...), 'float');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, array>
     */
    public static function array(): self
    {
        /** @var self<mixed, array> */
        return new self(\is_array(...), 'array');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, bool>
     */
    public static function bool(): self
    {
        /** @var self<mixed, bool> */
        return new self(\is_bool(...), 'bool');
    }

    /**
     * @psalm-pure
     *
     * @return self<mixed, null>
     */
    public static function null(): self
    {
        /** @var self<mixed, null> */
        return new self(\is_null(...), 'null');
    }

    /**
     * @psalm-pure
     *
     * @return self<array, list>
     */
    public static function list(): self
    {
        /** @var self<array, list> */
        return new self(\array_is_list(...), 'list');
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     *
     * @return Constraint<mixed, non-empty-array<non-empty-string, mixed>>
     */
    public static function shape(string $key, Constraint $constraint): Constraint
    {
        return self::array()->and(Shape::of($key, $constraint));
    }

    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    public function or(Constraint $constraint): Constraint
    {
        return OrConstraint::of($this, $constraint);
    }

    public function map(callable $map): Constraint
    {
        return Map::of($this, $map);
    }

    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
