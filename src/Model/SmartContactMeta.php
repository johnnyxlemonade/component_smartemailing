<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

final class SmartContactMeta
{
    public function __construct(
        private readonly ?string $uid,
        private readonly ?int    $version,
        private readonly ?string $createdAt,
        private readonly ?string $updatedAt,
        private readonly ?string $origin
    ) {}

    public function getUid(): ?string { return $this->uid; }
    public function getVersion(): ?int { return $this->version; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getOrigin(): ?string { return $this->origin; }
}
