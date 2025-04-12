<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\Validation;

/**
 * @internal
 * @template-covariant T
 * @template-covariant U
 * @implements Implementation<T, U>
 * @psalm-immutable
 */
final class Primitive implements Implementation
{
    /**
     * @param pure-Closure(T): bool $assert
     * @param non-empty-string $type
     */
    private function __construct(
        private \Closure $assert,
        private string $type,
    ) {
    }

    /**
     * @param T $value
     *
     * @return Validation<Failure, U>
     */
    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, U> */
        return match (($this->assert)($value)) {
            true => Validation::success($value),
            false => Validation::fail(Failure::of(
                "Value is not of type {$this->type}",
            )),
        };
    }

    /**
     * @internal
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
     * @internal
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
     * @internal
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
     * @internal
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
     * @internal
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
     * @internal
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
     * @internal
     * @psalm-pure
     *
     * @return Implementation<mixed, list<mixed>>
     */
    public static function list(): Implementation
    {
        /** @var self<array, list<mixed>> */
        $list = new self(\array_is_list(...), 'list');

        return AndConstraint::of(self::array(), $list);
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @return self<mixed, object>
     */
    public static function object(): self
    {
        /** @var self<mixed, object> */
        return new self(\is_object(...), 'object');
    }
}
