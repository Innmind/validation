<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @template-covariant T
 * @implements Constraint<mixed, T>
 * @psalm-immutable
 */
final class Is implements Constraint
{
    /** @var pure-callable(mixed): bool */
    private $assert;
    /** @var non-empty-string */
    private string $type;

    /**
     * @param pure-callable(mixed): bool $assert
     * @param non-empty-string $type
     */
    private function __construct(callable $assert, string $type)
    {
        $this->assert = $assert;
        $this->type = $type;
    }

    public function __invoke(mixed $value): Validation
    {
        return match (($this->assert)($value)) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of("Value is not of type {$this->type}")),
        };
    }

    /**
     * @psalm-pure
     *
     * @return self<string>
     */
    public static function string(): self
    {
        /** @var self<string> */
        return new self(\is_string(...), 'string');
    }

    /**
     * @psalm-pure
     *
     * @return self<int>
     */
    public static function int(): self
    {
        /** @var self<int> */
        return new self(\is_int(...), 'int');
    }

    /**
     * @psalm-pure
     *
     * @return self<float>
     */
    public static function float(): self
    {
        /** @var self<float> */
        return new self(\is_float(...), 'float');
    }

    /**
     * @psalm-pure
     *
     * @return self<array>
     */
    public static function array(): self
    {
        /** @var self<array> */
        return new self(\is_array(...), 'array');
    }

    public function and(Constraint $constraint): Constraint
    {
        return AndConstraint::of($this, $constraint);
    }

    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
