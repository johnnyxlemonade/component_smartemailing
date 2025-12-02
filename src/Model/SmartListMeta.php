<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

final class SmartListMeta
{
    public function __construct(
        private readonly ?string $guid,
        private readonly ?int    $version,
        private readonly ?string $publicName,
        private readonly ?string $notes,
        private readonly ?int    $alertIn,
        private readonly ?int    $alertOut,
        private readonly ?string $category,
        private readonly ?string $signature,
        private readonly ?string $segmentId,
        private readonly ?bool   $hidden,
        private readonly int     $totalContacts,
        private readonly ?bool   $protected,
        private readonly array   $notificationEmails,
        private readonly array   $data
    ) {}

    public function getGuid(): ?string { return $this->guid; }
    public function getVersion(): ?int { return $this->version; }
    public function getPublicName(): ?string { return $this->publicName; }
    public function getNotes(): ?string { return $this->notes; }
    public function getAlertIn(): ?int { return $this->alertIn; }
    public function getAlertOut(): ?int { return $this->alertOut; }
    public function getCategory(): ?string { return $this->category; }
    public function getSignature(): ?string { return $this->signature; }
    public function getSegmentId(): ?string { return $this->segmentId; }
    public function isHidden(): ?bool { return $this->hidden; }
    public function getTotalContacts(): int { return $this->totalContacts; }
    public function isProtected(): ?bool { return $this->protected; }
    public function getNotificationEmails(): array { return $this->notificationEmails; }
    public function getData(): array { return $this->data; }

    public static function fromArray(array $data): self
    {
        return new self(
            guid:               $data['guid'] ?? null,
            version:            $data['version'] ?? null,
            publicName:         $data['publicname'] ?? null,
            notes:              $data['notes'] ?? null,
            alertIn:            $data['alertIn'] ?? null,
            alertOut:           $data['alertOut'] ?? null,
            category:           $data['category'] ?? null,
            signature:          $data['signature'] ?? null,
            segmentId:          $data['segment_id'] ?? null,
            hidden:             $data['hidden'] ?? null,
            totalContacts:      (int)($data['totalContacts'] ?? 0),
            protected:          $data['protected'] ?? null,
            notificationEmails: $data['notification_emailadresses'] ?? [],
            data:               $data['data'] ?? [],
        );
    }
}
