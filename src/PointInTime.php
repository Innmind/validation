<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\Provider;
use Innmind\TimeContinuum\{
    Clock,
    Format,
    PointInTime as PointInTimeInterface,
};
use Innmind\Immutable\{
    Validation,
    Predicate,
};

/**
 * @implements Provider<string, PointInTimeInterface>
 * @psalm-immutable
 */
final class PointInTime implements Provider
{
    private Clock $clock;
    private Format $format;

    private function __construct(
        Clock $clock,
        Format $format,
    ) {
        $this->clock = $clock;
        $this->format = $format;
    }

    /**
     * @param string $value
     *
     * @return Validation<Failure, PointInTimeInterface>
     */
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

    #[\Override]
    public function toConstraint(): Constraint
    {
        /** @psalm-suppress InvalidArgument */
        return Constraint::of($this(...));
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
     *
     * @return Constraint<string, PointInTimeInterface>
     */
    public function withFailure(string $message): Constraint
    {
        return $this
            ->toConstraint()
            ->failWith($message);
    }

    /**
     * @template T
     *
     * @param Constraint<PointInTimeInterface, T> $constraint
     *
     * @return Constraint<string, T>
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
     * @param Constraint<string, T> $constraint
     *
     * @return Constraint<string, PointInTimeInterface|T>
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
     * @param callable(PointInTimeInterface): T $map
     *
     * @return Constraint<string, T>
     */
    public function map(callable $map): Constraint
    {
        return $this
            ->toConstraint()
            ->map($map);
    }

    /**
     * @return Predicate<PointInTimeInterface>
     */
    public function asPredicate(): Predicate
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
