<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\{
    Constraint,
    Failure,
    Has,
};
use Innmind\Immutable\Validation;

/**
 * @implements Implementation<mixed, non-empty-array<non-empty-string, mixed>>
 * @psalm-immutable
 */
final class Shape implements Implementation
{
    /**
     * @param non-empty-array<non-empty-string, Constraint<mixed, mixed>> $constraints
     * @param list<non-empty-string> $optional
     * @param array<non-empty-string, mixed> $defaults
     * @param array<non-empty-string, non-empty-string> $rename
     * @param ?\Closure(non-empty-string): non-empty-string $message
     */
    private function __construct(
        private array $constraints,
        private array $optional,
        private array $defaults,
        private array $rename,
        private ?\Closure $message,
    ) {
    }

    /**
     * @return Validation<Failure, non-empty-array<non-empty-string, mixed>>
     */
    #[\Override]
    public function __invoke(mixed $input): Validation
    {
        return Primitive::array()($input)->flatMap($this->validate(...));
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-array<non-empty-string, Constraint<mixed, mixed>> $constraints
     * @param list<non-empty-string> $optional
     * @param array<non-empty-string, mixed> $defaults
     * @param array<non-empty-string, non-empty-string> $rename
     * @param ?\Closure(non-empty-string): non-empty-string $message
     */
    public static function of(
        array $constraints,
        array $optional,
        array $defaults,
        array $rename,
        ?\Closure $message,
    ): self {
        return new self(
            $constraints,
            $optional,
            $defaults,
            $rename,
            $message,
        );
    }

    /**
     * @return Validation<Failure, non-empty-array<non-empty-string, mixed>>
     */
    private function validate(array $value): Validation
    {
        $optional = new \stdClass;
        /** @var Validation<Failure, non-empty-array<non-empty-string, mixed>> */
        $validation = Validation::success([]);

        foreach ($this->constraints as $key => $constraint) {
            $keyValidation = Has::key($key);

            if (!\is_null($this->message)) {
                $keyValidation = $keyValidation->withFailure($this->message);
            }

            if (\in_array($key, $this->optional, true)) {
                /** @psalm-suppress MixedArgumentTypeCoercion */
                $keyValidation = $keyValidation->or(Constraint::of(
                    static fn() => Validation::success($optional),
                ));
            }

            $ofType = Constraint::of(
                static fn($value) => match ($value) {
                    $optional => Validation::success($optional),
                    default => $constraint($value)->mapFailures(
                        static fn($failure) => $failure->under($key),
                    ),
                },
            );

            $validation = $validation->and(
                $keyValidation->and($ofType)($value),
                function($array, $value) use ($key, $optional) {
                    $concreteKey = $this->rename[$key] ?? $key;

                    if ($value !== $optional) {
                        /** @psalm-suppress MixedAssignment */
                        $array[$concreteKey] = $value;
                    } else if (\array_key_exists($key, $this->defaults)) {
                        /** @psalm-suppress MixedAssignment */
                        $array[$concreteKey] = $this->defaults[$key];
                    }

                    return $array;
                },
            );
        }

        return $validation;
    }
}
