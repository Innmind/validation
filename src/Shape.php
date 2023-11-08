<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Constraint<array, non-empty-array<non-empty-string, mixed>>
 * @psalm-immutable
 */
final class Shape implements Constraint
{
    /** @var non-empty-array<non-empty-string, Constraint<mixed, mixed>> */
    private array $constraints;

    /**
     * @param non-empty-array<non-empty-string, Constraint<mixed, mixed>> $constraints
     */
    private function __construct(array $constraints)
    {
        $this->constraints = $constraints;
    }

    public function __invoke(mixed $value): Validation
    {
        /** @var Validation<Failure, non-empty-array<non-empty-string, mixed>> */
        $validation = Validation::success([]);

        foreach ($this->constraints as $key => $constraint) {
            $ofType = Of::callable(
                static fn($value) => $constraint($value)->mapFailures(
                    static fn($failure) => $failure->under($key),
                ),
            );

            $validation = $validation->and(
                Has::key($key)->and($ofType)($value),
                static function($array, $value) use ($key) {
                    /** @psalm-suppress MixedAssignment */
                    $array[$key] = $value;

                    return $array;
                },
            );
        }

        return $validation;
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function of(string $key, Constraint $constraint): self
    {
        return new self([$key => $constraint]);
    }

    /**
     * @param non-empty-string $key
     */
    public function with(string $key, Constraint $constraint): self
    {
        $constraints = $this->constraints;
        $constraints[$key] = $constraint;

        return new self($constraints);
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
