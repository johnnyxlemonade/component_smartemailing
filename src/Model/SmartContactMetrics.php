<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

final class SmartContactMetrics
{
    public function __construct(
        private readonly ?string $lastEmailSentAt,
        private readonly ?string $lastOpenedAt,
        private readonly ?string $lastClickedAt,
        private readonly ?bool   $isHardbounced,
        private readonly ?int    $softBouncesInRow
    ) {}

    public function getLastEmailSentAt(): ?string { return $this->lastEmailSentAt; }
    public function getLastOpenedAt(): ?string { return $this->lastOpenedAt; }
    public function getLastClickedAt(): ?string { return $this->lastClickedAt; }
    public function isHardbounced(): ?bool { return $this->isHardbounced; }
    public function getSoftBouncesInRow(): ?int { return $this->softBouncesInRow; }
}
