<?php
declare(strict_types = 1);

namespace Innmind\Validation;

/**
 * @psalm-immutable
 */
final class Failure
{
    private KeyPath $path;
    /** @var non-empty-string */
    private string $message;

    /**
     * @param non-empty-string $message
     */
    private function __construct(KeyPath $path, string $message)
    {
        $this->path = $path;
        $this->message = $message;
    }

    /**
     * @psalm-pure
     *
     * @param non-empty-string $message
     */
    public static function of(string $message): self
    {
        return new self(KeyPath::root(), $message);
    }

    /**
     * @param non-empty-string $path
     */
    public function under(string $path): self
    {
        return new self($this->path->under($path), $this->message);
    }

    public function path(): KeyPath
    {
        return $this->path;
    }

    /**
     * @return non-empty-string
     */
    public function message(): string
    {
        return $this->message;
    }
}
