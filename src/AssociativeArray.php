<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Map,
    Predicate,
    Pair,
};

/**
 * @template K
 * @template V
 * @implements Provider<mixed, Map<K, V>>
 * @psalm-immutable
 */
final class AssociativeArray implements Provider
{
    private function __construct(
        /** @var Implementation<mixed, K>|Constraint<mixed, K> */
        private Implementation|Constraint $key,
        /** @var Implementation<mixed, V>|Constraint<mixed, V> */
        private Implementation|Constraint $value,
    ) {
    }

    /**
     * @return Validation<Failure, Map<K, V>>
     */
    public function __invoke(mixed $value): Validation
    {
        return Is::array()($value)->flatMap($this->validate(...));
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        return Constraint::of($this(...));
    }

    /**
     * @psalm-pure
     * @template A
     * @template B
     *
     * @param Implementation<mixed, A>|Provider<mixed, A>|Constraint<mixed, A> $key
     * @param Implementation<mixed, B>|Provider<mixed, B>|Constraint<mixed, B> $value
     *
     * @return self<A, B>
     */
    public static function of(Implementation|Provider|Constraint $key, Implementation|Provider|Constraint $value): self
    {
        if ($key instanceof Provider) {
            $key = $key->toConstraint();
        }

        if ($value instanceof Provider) {
            $value = $value->toConstraint();
        }

        return new self($key, $value);
    }

    /**
     * @template T
     *
     * @param Constraint<Map<K, V>, T> $constraint
     *
     * @return Constraint<mixed, T>
     */
    public function and(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->and($constraint);
    }

    /**
     * @template T
     *
     * @param Constraint<mixed, T> $constraint
     *
     * @return Constraint<mixed, Map<K, V>|T>
     */
    public function or(Constraint $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->or($constraint);
    }

    /**
     * @template T
     *
     * @param callable(Map<K, V>): T $map
     *
     * @return Constraint<mixed, T>
     */
    public function map(callable $map): Constraint
    {
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @return Predicate<Map<K, V>>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
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
