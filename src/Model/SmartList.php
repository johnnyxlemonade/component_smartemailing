<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartList
 *
 * Model reprezentující seznam kontaktů (list) ve SmartEmailing API.
 * Obsahuje základní informace o seznamu, odesílateli, metadatech
 * a poskytuje statickou konstrukci z API odpovědi.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartList
{
    public function __construct(
        private readonly int    $id,
        private readonly string $name,
        private readonly string $created,
        private readonly int    $activeContacts,
        private readonly string $senderName,
        private readonly string $senderEmail,
        private readonly string $replyTo,
        private readonly SmartListMeta $meta
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function getActiveContacts(): int
    {
        return $this->activeContacts;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function getMeta(): SmartListMeta
    {
        return $this->meta;
    }

    public static function fromApiResponse(array $data): self
    {
        $meta = new SmartListMeta(
            guid:               $data['guid'] ?? null,
            version:            isset($data['version']) ? (int)$data['version'] : null,
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
            notificationEmails: $data['notificationEmails'] ?? [],
            data:               $data['data'] ?? []
        );

        return new self(
            id:             (int)($data['id'] ?? 0),
            name:           (string)($data['name'] ?? ''),
            created:        (string)($data['created'] ?? ''),
            activeContacts: (int)($data['activeContacts'] ?? 0),
            senderName:     (string)($data['sendername'] ?? ''),
            senderEmail:    (string)($data['senderemail'] ?? ''),
            replyTo:        (string)($data['replyto'] ?? ''),
            meta:           $meta
        );
    }

}
