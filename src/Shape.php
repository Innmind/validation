<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Constraint<mixed, non-empty-array<non-empty-string, mixed>>
 * @psalm-immutable
 */
final class Shape implements Constraint
{
    /** @var non-empty-array<non-empty-string, Constraint<mixed, mixed>> */
    private array $constraints;
    /** @var list<non-empty-string> */
    private array $optional;
    /** @var ?callable(non-empty-string): non-empty-string */
    private $message;

    /**
     * @param non-empty-array<non-empty-string, Constraint<mixed, mixed>> $constraints
     * @param list<non-empty-string> $optional
     * @param ?callable(non-empty-string): non-empty-string $message
     */
    private function __construct(
        array $constraints,
        array $optional,
        ?callable $message = null,
    ) {
        $this->constraints = $constraints;
        $this->optional = $optional;
        $this->message = $message;
    }

    public function __invoke(mixed $value): Validation
    {
        return Is::array()($value)->flatMap($this->validate(...));
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function of(string $key, Constraint $constraint): self
    {
        return new self([$key => $constraint], []);
    }

    /**
     * @param non-empty-string $key
     */
    public function with(string $key, Constraint $constraint): self
    {
        $constraints = $this->constraints;
        $constraints[$key] = $constraint;

        return new self($constraints, $this->optional, $this->message);
    }

    /**
     * @param non-empty-string $key
     */
    public function optional(string $key, Constraint $constraint = null): self
    {
        $optional = $this->optional;
        $optional[] = $key;
        $constraints = $this->constraints;

        if ($constraint instanceof Constraint) {
            $constraints[$key] = $constraint;
        }

        return new self($constraints, $optional, $this->message);
    }

    /**
     * @param callable(non-empty-string): non-empty-string $message
     */
    public function withKeyFailure(callable $message): self
    {
        return new self($this->constraints, $this->optional, $message);
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
                $keyValidation = $keyValidation->or(Of::callable(
                    static fn() => Validation::success($optional),
                ));
            }

            $ofType = Of::callable(
                static fn($value) => match ($value) {
                    $optional => Validation::success($optional),
                    default => $constraint($value)->mapFailures(
                        static fn($failure) => $failure->under($key),
                    ),
                },
            );

            $validation = $validation->and(
                $keyValidation->and($ofType)($value),
                static function($array, $value) use ($key, $optional) {
                    if ($value !== $optional) {
                        /** @psalm-suppress MixedAssignment */
                        $array[$key] = $value;
                    }

                    return $array;
                },
            );
        }

        return $validation;
    }
}
