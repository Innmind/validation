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

    private function __construct(Clock $clock, Format $format)
    {
        $this->clock = $clock;
        $this->format = $format;
    }

    public function __invoke(mixed $value): Validation
    {
        return $this->clock->at($value, $this->format)->match(
            static fn($point) => Validation::success($point),
            fn() => Validation::fail(Failure::of("Value is not a date of format {$this->format->toString()}")),
        );
    }

    /**
     * @psalm-pure
     */
    public static function ofFormat(Clock $clock, Format $format): self
    {
        return new self($clock, $format);
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
