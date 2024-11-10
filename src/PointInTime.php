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
 * @implements Constraint<string, PointInTimeInterface>
 * @psalm-immutable
 */
final class PointInTime implements Constraint
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

    public function __invoke(mixed $value): Validation
    {
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
}
