<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

final class SmartContactEngagement
{
    public function __construct(
        private readonly ?string $level,
        private readonly ?int    $score,
        private readonly ?string $calculatedAt,
        private readonly ?int    $daysSinceLastEmail
    ) {}

    public function getLevel(): ?string { return $this->level; }
    public function getScore(): ?int { return $this->score; }
    public function getCalculatedAt(): ?string { return $this->calculatedAt; }
    public function getDaysSinceLastEmail(): ?int { return $this->daysSinceLastEmail; }
}
