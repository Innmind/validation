<?php
declare(strict_types = 1);

namespace Innmind\Validation;

use Innmind\Immutable\Set;

/**
 * @psalm-immutable
 */
final class Failure
{
    /**
     * @param non-empty-string $message
     * @param Set<\UnitEnum> $tags
     */
    private function __construct(
        private KeyPath $path,
        private string $message,
        private Set $tags,
    ) {
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $message
     */
    #[\NoDiscard]
    public static function of(string $message): self
    {
        return new self(KeyPath::root(), $message, Set::of());
    }

    /**
     * @param non-empty-string $path
     */
    #[\NoDiscard]
    public function under(string $path): self
    {
        return new self(
            $this->path->under($path),
            $this->message,
            $this->tags,
        );
    }

    #[\NoDiscard]
    public function tag(\UnitEnum $tag): self
    {
        return new self(
            $this->path,
            $this->message,
            ($this->tags)($tag),
        );
    }

    #[\NoDiscard]
    public function path(): KeyPath
    {
        return $this->path;
    }

    /**
     * @return non-empty-string
     */
    #[\NoDiscard]
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return Set<\UnitEnum>
     */
    #[\NoDiscard]
    public function tags(): Set
    {
        return $this->tags;
    }
}
