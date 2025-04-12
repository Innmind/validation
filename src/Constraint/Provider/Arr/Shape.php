<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint\Provider\Arr;

use Innmind\Validation\{
    Constraint,
    Constraint\Provider,
    Constraint\Implementation,
    Constraint\Like,
};

/**
 * @implements Provider<mixed, non-empty-array<non-empty-string, mixed>>
 * @psalm-immutable
 */
final class Shape implements Provider
{
    /** @use Like<mixed, non-empty-array<non-empty-string, mixed>> */
    use Like;

    /**
     * @param pure-Closure(Implementation): Constraint $build
     * @param non-empty-array<non-empty-string, Constraint<mixed, mixed>> $constraints
     * @param list<non-empty-string> $optional
     * @param array<non-empty-string, mixed> $defaults
     * @param array<non-empty-string, non-empty-string> $rename
     * @param ?\Closure(non-empty-string): non-empty-string $message
     */
    private function __construct(
        private \Closure $build,
        private array $constraints,
        private array $optional,
        private array $defaults,
        private array $rename,
        private ?\Closure $message,
    ) {
    }

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @var Constraint<mixed, non-empty-array<non-empty-string, mixed>> */
        return ($this->build)(Constraint\Shape::of(
            $this->constraints,
            $this->optional,
            $this->defaults,
            $this->rename,
            $this->message,
        ));
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param pure-Closure(Implementation): Constraint $build
     * @param non-empty-string $key
     */
    public static function of(
        \Closure $build,
        string $key,
        Provider|Constraint $constraint,
    ): self {
        if ($constraint instanceof Provider) {
            $constraint = $constraint->toConstraint();
        }

        return new self(
            $build,
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
    public function with(string $key, Provider|Constraint $constraint): self
    {
        if ($constraint instanceof Provider) {
            $constraint = $constraint->toConstraint();
        }

        $constraints = $this->constraints;
        $constraints[$key] = $constraint;

        return new self(
            $this->build,
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
    public function optional(string $key, Provider|Constraint|null $constraint = null): self
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
            $this->build,
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
            $this->build,
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
            $this->build,
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
            $this->build,
            $this->constraints,
            $this->optional,
            $this->defaults,
            $this->rename,
            \Closure::fromCallable($message),
        );
    }
}
