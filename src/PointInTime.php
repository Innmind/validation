<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\TimeContinuum\{
    Clock,
    Format,
    PointInTime as PointInTimeInterface,
};
use Innmind\Immutable\{
    Validation,
    Predicate as PredicateInterface,
};

/**
 * @implements Constraint\Implementation<string, PointInTimeInterface>
 * @psalm-immutable
 */
final class PointInTime implements Constraint\Implementation
{
    private Clock $clock;
    private Format $format;
    /** @var ?non-empty-string */
    private ?string $message;

    /**
     * @param ?non-empty-string $message
     */
    private function __construct(
        Clock $clock,
        Format $format,
        ?string $message = null,
    ) {
        $this->clock = $clock;
        $this->format = $format;
        $this->message = $message;
    }

    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        if ($value === '') {
            return Validation::fail(Failure::of(
                $this->message ?? "Value is not a date of format {$this->format->toString()}",
            ));
        }

        return $this->clock->at($value, $this->format)->match(
            static fn($point) => Validation::success($point),
            fn() => Validation::fail(Failure::of(
                $this->message ?? "Value is not a date of format {$this->format->toString()}",
            )),
        );
    }

    /**
     * @psalm-pure
     */
    public static function ofFormat(Clock $clock, Format $format): self
    {
        return new self($clock, $format);
    }

    /**
     * @param non-empty-string $message
     */
    public function withFailure(string $message): self
    {
        return new self($this->clock, $this->format, $message);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<PointInTimeInterface, T> $constraint
     *
     * @return Constraint\Implementation<string, T>
     */
    #[\Override]
    public function and(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return AndConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param Constraint\Implementation<string, T> $constraint
     *
     * @return Constraint\Implementation<string, PointInTimeInterface|T>
     */
    #[\Override]
    public function or(Constraint\Implementation $constraint): Constraint\Implementation
    {
        return OrConstraint::of($this, $constraint);
    }

    /**
     * @template T
     *
     * @param callable(PointInTimeInterface): T $map
     *
     * @return Constraint\Implementation<string, T>
     */
    #[\Override]
    public function map(callable $map): Constraint\Implementation
    {
        return Map::of($this, $map);
    }

    /**
     * @return PredicateInterface<PointInTimeInterface>
     */
    #[\Override]
    public function asPredicate(): PredicateInterface
    {
        return Predicate::of($this);
    }
}
