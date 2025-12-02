<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing;

final class SmartEmailingResponse
{
    public function __construct(
        public readonly bool    $success,
        public readonly mixed   $data = null,
        public readonly ?string $message = null
    ) {}

    public static function ok(mixed $data = null): self
    {
        return new self(true, $data);
    }

    public static function error(string $message, mixed $data = null): self
    {
        return new self(false, $data, $message);
    }
}
