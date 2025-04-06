<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Provider<mixed, non-empty-array<non-empty-string, mixed>>
 * @psalm-immutable
 */
final class Shape implements Provider
{
    /** @var non-empty-array<non-empty-string, Implementation<mixed, mixed>|Constraint<mixed, mixed>> */
    private array $constraints;
    /** @var list<non-empty-string> */
    private array $optional;
    /** @var array<non-empty-string, mixed> */
    private array $defaults;
    /** @var array<non-empty-string, non-empty-string> */
    private array $rename;
    /** @var ?callable(non-empty-string): non-empty-string */
    private $message;

    /**
     * @param non-empty-array<non-empty-string, Implementation<mixed, mixed>|Constraint<mixed, mixed>> $constraints
     * @param list<non-empty-string> $optional
     * @param array<non-empty-string, mixed> $defaults
     * @param array<non-empty-string, non-empty-string> $rename
     * @param ?callable(non-empty-string): non-empty-string $message
     */
    private function __construct(
        array $constraints,
        array $optional,
        array $defaults,
        array $rename,
        ?callable $message,
    ) {
        $this->constraints = $constraints;
        $this->optional = $optional;
        $this->defaults = $defaults;
        $this->rename = $rename;
        $this->message = $message;
    }

    /**
     * @return Validation<Failure, non-empty-array<non-empty-string, mixed>>
     */
    public function __invoke(mixed $input): Validation
    {
        return $this->toConstraint()($input);
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        $validate = Constraint::of($this->validate(...));

        return Is::array()->and($validate);
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $key
     */
    public static function of(string $key, Implementation|Provider|Constraint $constraint): self
    {
        if ($constraint instanceof Provider) {
            $constraint = $constraint->toConstraint();
        }

        return new self(
            [$key => $constraint],
            [],
            [],
            [],
            null,
        );
    }

    /**
     * @param non-empty-string $key
     */
    public function with(string $key, Implementation|Provider|Constraint $constraint): self
    {
        if ($constraint instanceof Provider) {
            $constraint = $constraint->toConstraint();
        }

        $constraints = $this->constraints;
        $constraints[$key] = $constraint;

        return new self(
            $constraints,
            $this->optional,
            $this->defaults,
            $this->rename,
            $this->message,
        );
    }

    /**
     * @param non-empty-string $key
     */
    public function optional(string $key, Implementation|Provider|Constraint|null $constraint = null): self
    {
        $optional = $this->optional;
        $optional[] = $key;
        $constraints = $this->constraints;

        if (!\is_null($constraint)) {
            if ($constraint instanceof Provider) {
                $constraint = $constraint->toConstraint();
            }

            $constraints[$key] = $constraint;
        }

        return new self(
            $constraints,
            $optional,
            $this->defaults,
            $this->rename,
            $this->message,
        );
    }

    /**
     * @param non-empty-string $key
     */
    public function default(string $key, mixed $value): self
    {
        if (!\in_array($key, $this->optional, true)) {
            throw new \LogicException("No optional key $key defined");
        }

        $defaults = $this->defaults;
        /** @psalm-suppress MixedAssignment */
        $defaults[$key] = $value;

        return new self(
            $this->constraints,
            $this->optional,
            $defaults,
            $this->rename,
            $this->message,
        );
    }

    /**
     * @param non-empty-string $from
     * @param non-empty-string $to
     */
    public function rename(string $from, string $to): self
    {
        $rename = $this->rename;
        $rename[$from] = $to;

        return new self(
            $this->constraints,
            $this->optional,
            $this->defaults,
            $rename,
            $this->message,
        );
    }

    /**
     * @param callable(non-empty-string): non-empty-string $message
     */
    public function withKeyFailure(callable $message): self
    {
        return new self(
            $this->constraints,
            $this->optional,
            $this->defaults,
            $this->rename,
            $message,
        );
    }

    /**
     * @template T
     *
     * @param Constraint<non-empty-array<non-empty-string, mixed>, T> $constraint
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
     * @return Constraint<mixed, non-empty-array<non-empty-string, mixed>|T>
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
     * @param callable(non-empty-array<non-empty-string, mixed>): T $map
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
     * @return PredicateInterface<non-empty-array<non-empty-string, mixed>>
     */
    public function asPredicate(): PredicateInterface
    {
        return $this
            ->toConstraint()
            ->asPredicate();
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
