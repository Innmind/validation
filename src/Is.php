<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Is
{
    private function __construct()
    {
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, string>
     */
    public static function string(): Constraint
    {
        return Constraint::string();
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, int>
     */
    public static function int(): Constraint
    {
        return Constraint::int();
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, float>
     */
    public static function float(): Constraint
    {
        return Constraint::float();
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, array>
     */
    public static function array(): Constraint
    {
        return Constraint::array();
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, bool>
     */
    public static function bool(): Constraint
    {
        return Constraint::bool();
    }

    /**
     * @psalm-pure
     *
     * @return Constraint<mixed, null>
     */
    public static function null(): Constraint
    {
        return Constraint::null();
    }

    /**
     * @psalm-pure
     *
     * @template E
     *
     * @param Implementation<mixed, E>|Provider<mixed, E>|Constraint<mixed, E>|null $each
     *
     * @return Constraint<mixed, list<E>>
     */
    public static function list(Implementation|Provider|Constraint|null $each = null): Constraint
    {
        $constraint = Constraint::list();

        return match ($each) {
            null => $constraint,
            default => $constraint->and(Each::of($each)),
        };
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function shape(string $key, Implementation|Provider|Constraint $constraint): Shape
    {
        return Shape::of($key, $constraint);
    }

    /**
     * @psalm-pure
     * @template K
     * @template V
     *
     * @param Implementation<mixed, K>|Provider<mixed, K>|Constraint<mixed, K> $key
     * @param Implementation<mixed, V>|Provider<mixed, V>|Constraint<mixed, V> $value
     *
     * @return AssociativeArray<K, V>
     */
    public static function associativeArray(
        Implementation|Provider|Constraint $key,
        Implementation|Provider|Constraint $value,
    ): AssociativeArray {
        return AssociativeArray::of($key, $value);
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param ?non-empty-string $message
     *
     * @return Constraint<Maybe<V>, V>
     */
    public static function just(?string $message = null): Constraint
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return Constraint::of(static fn(Maybe $value) => $value->match(
            Validation::success(...),
            static fn() => Validation::fail(Failure::of(
                $message ?? 'No value was provided',
            )),
        ));
    }

    /**
     * @psalm-pure
     * @template V
     *
     * @param V $value
     * @param ?non-empty-string $message
     *
     * @return Constraint<mixed, V>
     */
    public static function value(mixed $value, ?string $message = null): Constraint
    {
        return Constraint::of(static fn(mixed $in) => match ($in) {
            $value => Validation::success($value),
            default => Validation::fail(Failure::of(
                $message ?? \sprintf(
                    'Not of expected value of type %s',
                    \gettype($value),
                ),
            )),
        });
    }
}
