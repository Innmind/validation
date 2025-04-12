<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\Immutable\{
    Validation,
    Map,
    Pair,
};

/**
 * @internal
 * @template K of array-key
 * @template V
 * @implements Implementation<mixed, Map<K, V>>
 * @psalm-immutable
 */
final class AssociativeArray implements Implementation
{
    private function __construct(
        /** @var Implementation<mixed, K> */
        private Implementation $key,
        /** @var Implementation<mixed, V> */
        private Implementation $value,
    ) {
    }

    /**
     * @return Validation<Failure, Map<K, V>>
     */
    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        return Primitive::array()($value)->flatMap($this->validate(...));
    }

    /**
     * @internal
     * @psalm-pure
     * @template A of array-key
     * @template B
     *
     * @param Implementation<mixed, A> $key
     * @param Implementation<mixed, B> $value
     *
     * @return self<A, B>
     */
    public static function of(Implementation $key, Implementation $value): self
    {
        return new self($key, $value);
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
