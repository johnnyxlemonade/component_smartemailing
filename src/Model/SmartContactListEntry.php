<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

final class SmartContactListEntry
{
    public function __construct(
        private readonly int     $listId,
        private readonly string  $status,
        private readonly ?string $added,
        private readonly ?string $updated
    ) {}

    public function getListId(): int { return $this->listId; }
    public function getStatus(): string { return $this->status; }
    public function getAdded(): ?string { return $this->added; }
    public function getUpdated(): ?string { return $this->updated; }
}
