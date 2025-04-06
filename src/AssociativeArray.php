<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Map,
    Predicate,
    Pair,
};

/**
 * @template K
 * @template V
 * @implements Constraint\Implementation<mixed, Map<K, V>>
 * @implements Constraint\Provider<mixed, Map<K, V>>
 * @psalm-immutable
 */
final class AssociativeArray implements Constraint\Implementation, Constraint\Provider
{
    private function __construct(
        /** @var Constraint\Implementation<mixed, K> */
        private Constraint\Implementation $key,
        /** @var Constraint\Implementation<mixed, V> */
        private Constraint\Implementation $value,
    ) {
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return Is::array()($value)->flatMap($this->validate(...));
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        return Constraint::build($this);
    }

    /**
     * @psalm-pure
     * @template A
     * @template B
     *
     * @param Constraint\Implementation<mixed, A> $key
     * @param Constraint\Implementation<mixed, B> $value
     *
     * @return self<A, B>
     */
    public static function of(Constraint\Implementation $key, Constraint\Implementation $value): self
    {
        return new self($key, $value);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<Map<K, V>, T> $constraint
     *
     * @return Constraint\Implementation<mixed, T>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<mixed, T> $constraint
     *
     * @return Constraint\Implementation<mixed, Map<K, V>|T>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(Map<K, V>): T $map
     *
     * @return Constraint\Implementation<mixed, T>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return namespace\Map::of($this, $map);
    }

    /**
     * @return Predicate<Map<K, V>>
     */
    #[\Override]
    public function asPredicate(): Predicate
    {
        return namespace\Predicate::of($this);
    }

    /**
     * @return Validation<Failure, Map<K, V>>
     */
    private function validate(array $array): Validation
    {
        /** @var Validation<Failure, Map<K, V>> */
        $validation = Validation::success(Map::of());

        /** @var mixed $value */
        foreach ($array as $key => $value) {
            /**
             * @psalm-suppress ArgumentTypeCoercion Due to the non-empty-string for value failures
             * @var Validation<Failure, Pair<K, V>>
             */
            $pair = ($this->key)($key)
                ->mapFailures(
                    static fn($failure) => $failure->under(\sprintf(
                        'key(%s)',
                        $key,
                    )),
                )
                ->flatMap(
                    fn($parsedKey) => ($this->value)($value)
                        ->map(
                            static fn($value) => new Pair($parsedKey, $value),
                        )
                        ->mapFailures(
                            static fn($failure) => $failure->under(match ($key) {
                                '' => "''",
                                default => (string) $key,
                            }),
                        ),
                );

            $validation = $validation->and(
                $pair,
                static fn($map, $pair) => ($map)($pair->key(), $pair->value()),
            );
        }

        return $validation;
    }
}
