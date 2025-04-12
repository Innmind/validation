<?php
declare(strict_types = 1);

namespace Innmind\Validation\Constraint;

use Innmind\Validation\Failure;
use Innmind\TimeContinuum\{
    Clock,
    Format,
    PointInTime as PointInTimeInterface,
};
use Innmind\Immutable\Validation;

/**
 * @internal
 * @implements Implementation<string, PointInTimeInterface>
 * @psalm-immutable
 */
final class PointInTime implements Implementation
{
    private function __construct(
        private Clock $clock,
        private Format $format,
    ) {
    }

    /**
     * @param string $value
     *
     * @return Validation<Failure, PointInTimeInterface>
     */
    #[\Override]
    public function __invoke(mixed $value): Validation
    {
        if ($value === '') {
            return Validation::fail(Failure::of(
                "Value is not a date of format {$this->format->toString()}",
            ));
        }

        return $this->clock->at($value, $this->format)->match(
            static fn($point) => Validation::success($point),
            fn() => Validation::fail(Failure::of(
                "Value is not a date of format {$this->format->toString()}",
            )),
        );
    }

    /**
     * @internal
     * @psalm-pure
     */
    public static function ofFormat(Clock $clock, Format $format): self
    {
        return new self($clock, $format);
    }
}
