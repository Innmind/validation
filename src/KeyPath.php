<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Sequence;

/**
 * @psalm-immutable
 */
final class KeyPath
{
    /** @var Sequence<non-empty-string> */
    private Sequence $parts;

    /**
     * @param Sequence<non-empty-string> $parts
     */
    private function __construct(Sequence $parts)
    {
        $this->parts = $parts;
    }

    /**
     * @psalm-pure
     */
    public static function root(): self
    {
        return new self(Sequence::of());
    }

    /**
     * @param non-empty-string $path
     */
    public function under(string $path): self
    {
        return new self(($this->parts)($path));
    }

    /**
     * @template T
     *
     * @param callable(non-empty-string, ...non-empty-string): T $path
     * @param callable(): T $root
     *
     * @return T
     */
    public function match(callable $path, callable $root)
    {
        return $this
            ->parts
            ->reverse()
            ->match(
                static fn($first, $rest) => $path($first, ...$rest->toList()),
                $root,
            );
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->match(
            static fn($part, ...$parts) => \implode('.', [$part, ...$parts]),
            static fn() => '$',
        );
    }
}
