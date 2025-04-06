<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Validation\Constraint\{
    Implementation,
    Provider,
};
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
 * @implements Provider<string, PointInTimeInterface>
 * @psalm-immutable
 */
final class PointInTime implements Provider
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

    /**
     * @param string $value
     *
     * @return Validation<Failure, PointInTimeInterface>
     */
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
     */
    public function withFailure(string $message): self
    {
        return new self($this->clock, $this->format, $message);
    }

    /**
     * @template T
     *
     * @param Implementation<PointInTimeInterface, T> $constraint
     *
     * @return Constraint<string, T>
     */
    public function and(Implementation $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->and(Constraint::build($constraint));
    }

    /**
     * @template T
     *
     * @param Implementation<string, T> $constraint
     *
     * @return Constraint<string, PointInTimeInterface|T>
     */
    public function or(Implementation $constraint): Constraint
    {
        return $this
            ->toConstraint()
            ->or(Constraint::build($constraint));
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
     * @return PredicateInterface<PointInTimeInterface>
     */
    public function asPredicate(): PredicateInterface
    {
        return $this
            ->toConstraint()
            ->asPredicate();
    }
}
