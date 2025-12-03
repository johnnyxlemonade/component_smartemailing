<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContact
 *
 * Reprezentace kontaktu načteného ze SmartEmailing API.
 * Obsahuje základní identitu, metadata, engagement, metriky,
 * seznamy a vlastní pole. Poskytuje jednotné objektové rozhraní
 * pro práci s kontaktními daty.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartContact
{
    /** základní identita */
    public function __construct(
        private readonly string  $id,
        private readonly string  $guid,
        private readonly string  $email,
        private readonly string  $name,
        private readonly string  $surname,
        private readonly string  $created,
        private readonly string  $language,
        private readonly bool    $confirmed,
        private readonly int     $listCount,

        /** rozšířená metadata */
        private readonly ?SmartContactMeta      $meta = null,
        private readonly ?SmartContactEngagement  $engagement = null,
        private readonly ?SmartContactMetrics   $metrics = null,
        private readonly array                  $lists = [],
        private readonly array                  $customFields = []
    ) {}

    // Gettery původní
    public function getId(): string { return $this->id; }
    public function getGuid(): string { return $this->guid; }
    public function getEmail(): string { return $this->email; }
    public function getName(): string { return $this->name; }
    public function getSurname(): string { return $this->surname; }
    public function getCreated(): string { return $this->created; }
    public function getLanguage(): string { return $this->language; }
    public function isConfirmed(): bool { return $this->confirmed; }
    public function getListCount(): int { return $this->listCount; }

    // nové gettery
    public function getMeta(): ?SmartContactMeta { return $this->meta; }
    public function getEngagement(): ?SmartContactEngagement { return $this->engagement; }
    public function getMetrics(): ?SmartContactMetrics { return $this->metrics; }

    /** @return SmartContactListEntry[] */
    public function getLists(): array { return $this->lists; }

    /** @return array<string,mixed> */
    public function getCustomFields(): array { return $this->customFields; }

    public function getFullName(): string
    {
        $full = trim($this->name . ' ' . $this->surname);
        return $full !== '' ? $full : $this->email;
    }

    public static function fromApiResponse(array $data): self
    {
        $meta = new SmartContactMeta(
            uid:         $data['uid'] ?? null,
            version:     isset($data['version']) ? (int)$data['version'] : null,
            createdAt:   $data['created_at'] ?? null,
            updatedAt:   $data['updated_at'] ?? null,
            origin:      $data['origin'] ?? null
        );

        $engagement = isset($data['engagement']) && is_array($data['engagement'])
            ? new SmartContactEngagement(
                level: $data['engagement']['level'] ?? null,
                score: isset($data['engagement']['score']) ? (int)$data['engagement']['score'] : null,
                calculatedAt: $data['engagement']['calculated_at'] ?? null,
                daysSinceLastEmail: isset($data['engagement']['number_of_days_since_last_email'])
                    ? (int)$data['engagement']['number_of_days_since_last_email']
                    : null
            )
            : null;

        $metricsEmail = $data['metrics']['email'] ?? null;
        $metrics = is_array($metricsEmail)
            ? new SmartContactMetrics(
                lastEmailSentAt: $metricsEmail['last_email_sent_at'] ?? null,
                lastOpenedAt:    $metricsEmail['last_opened_at'] ?? null,
                lastClickedAt:   $metricsEmail['last_clicked_at'] ?? null,
                isHardbounced:   $metricsEmail['is_hardbounced'] ?? null,
                softBouncesInRow: isset($metricsEmail['softbounces_in_row'])
                    ? (int)$metricsEmail['softbounces_in_row']
                    : null
            )
            : null;

        $lists = [];
        foreach (($data['contactlists'] ?? []) as $entry) {
            if (!is_array($entry)) continue;
            $lists[] = new SmartContactListEntry(
                listId: (int)($entry['contactlist_id'] ?? 0),
                status: (string)($entry['status'] ?? ''),
                added:  $entry['added'] ?? null,
                updated: $entry['updated'] ?? null
            );
        }

        return new self(
            id:         (string)($data['id'] ?? ''),
            guid:       (string)($data['guid'] ?? ''),
            email:      (string)($data['emailaddress'] ?? ''),
            name:       (string)($data['name'] ?? ''),
            surname:    (string)($data['surname'] ?? ''),
            created:    (string)($data['created_at'] ?? ''),
            language:   (string)($data['language'] ?? 'cs_CZ'),
            confirmed:  (bool)($data['confirmed'] ?? false),
            listCount:  (int)($data['list_count'] ?? 0),

            meta:         $meta,
            engagement:   $engagement,
            metrics:      $metrics,
            lists:        $lists,
            customFields: ($data['fields'] ?? [])
        );
    }

}
