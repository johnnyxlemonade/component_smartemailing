<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

use function in_array;
use function trim;

/**
 * SmartEmailingFormatter
 *
 * Převádí raw API odpovědi do jednotných struktur.
 */
final class SmartEmailingFormatter
{
    public function getPing(array $response = []): bool
    {
        return (string)($response['status'] ?? 'NOT') === 'ok';
    }

    public function getAccountId(array $response = []): string
    {
        return (string)($response['account_id'] ?? '0');
    }

    public function checkLogin(array $response = []): bool
    {
        return (string)($response['status'] ?? 'NOT') === 'ok';
    }

    /**
     * Formát mapy kontaktů vrácené při importu.
     * [ [contact_id=>..., emailaddress=>...], ... ]
     */
    public function createContact(array $contactsMap = []): array
    {
        $result = [];

        foreach ($contactsMap as $item) {
            $contactId = (string)($item['contact_id'] ?? '');
            $email     = (string)($item['emailaddress'] ?? '');

            if ($contactId !== '' && $email !== '') {
                $result[$contactId] = $email;
            }
        }

        return $result;
    }

    public function deleteContact(array $response = []): bool
    {
        return (string)($response['status'] ?? 'NOT') === 'ok';
    }

    /**
     * Formát kontaktu nebo kontaktů.
     */
    public function getContact(array $listArray = []): array
    {
        $result = [];

        foreach ($listArray as $item) {
            $formatted = $this->formatContactItem($item);
            if ($formatted !== []) {
                $result[$formatted['id']] = $formatted;
            }
        }

        return $result;
    }

    /**
     * Formát seznamů (contactlists).
     */
    public function getList(array $listArray = []): array
    {
        $result = [];

        foreach ($listArray as $i) {
            $formatted = $this->formatListItem($i);
            if ($formatted !== []) {
                $result[$formatted['id']] = $formatted;
            }
        }

        return $result;
    }

    /**
     * Kontrola existence listId.
     */
    public function checkListId(string|int $listId, array $listArray = []): bool
    {
        $lists = $this->getList($listArray);

        return in_array(
            $listId,
            array_keys($lists),
            is_int($listId)
        );
    }

    private function formatContactItem(array $item): array
    {
        $id = (string)($item['id'] ?? '');
        if ($id === '') {
            return [];
        }

        return [
            'id'               => $id,
            'guid'             => $item['guid'] ?? '',
            'contactEmail'     => (string)($item['emailaddress'] ?? ''),
            'contactName'      => (string)($item['name'] ?? ''),
            'contactSurname'   => (string)($item['surname'] ?? ''),
            'contactCreated'   => (string)($item['created'] ?? ''),
            'contactLang'      => (string)($item['language'] ?? 'cs_CZ'),
            'contactConfirmed' => (bool)($item['is_confirmed'] ?? false),
            'contactListItems' => isset($item['contactlists']) && is_array($item['contactlists'])
                ? count($item['contactlists'])
                : 0,

            'meta'       => $this->formatContactMeta($item),
            'engagement' => $this->formatEngagement($item),
            'metrics'    => $this->formatMetrics($item),

            'fields'        => $item['fields'] ?? [],
            'contactlists'  => $this->formatContactLists($item),
        ];
    }

    private function formatListItem(array $item): array
    {
        $id = (int)($item['id'] ?? 0);
        if ($id <= 0) {
            return [];
        }

        return [
            'id'             => $id,
            'name'           => (string)($item['name'] ?? ''),
            'created'        => (string)($item['created'] ?? ''),
            'activeContacts' => (int)($item['activeContacts'] ?? 0),

            'sendername'     => (string)($item['sendername'] ?? ''),
            'senderemail'    => (string)($item['senderemail'] ?? ''),
            'replyto'        => (string)($item['replyto'] ?? ''),

            // metadata
            'guid'               => $item['guid'] ?? null,
            'version'            => $item['version'] ?? null,
            'publicname'         => $item['publicname'] ?? null,
            'notes'              => $item['notes'] ?? null,
            'alertIn'            => $item['alertIn'] ?? null,
            'alertOut'           => $item['alertOut'] ?? null,
            'category'           => $item['category'] ?? null,
            'signature'          => $item['signature'] ?? null,
            'segment_id'         => $item['segment_id'] ?? null,
            'hidden'             => $item['hidden'] ?? null,
            'totalContacts'      => (int)($item['totalContacts'] ?? 0),
            'protected'          => $item['protected'] ?? null,
            'notificationEmails' => $item['notification_emailadresses'] ?? [],
            'data'               => $item['data'] ?? [],
        ];
    }

    private function formatContactMeta(array $item): array
    {
        return [
            'uid'             => $item['uid'] ?? null,
            'version'         => isset($item['version']) ? (int)$item['version'] : null,
            'created_at'      => $item['created_at'] ?? null,
            'last_updated_at' => $item['last_updated_at'] ?? null,
            'origin'          => $item['origin'] ?? null,
        ];
    }

    private function formatEngagement(array $item): ?array
    {
        if (!isset($item['engagement'])) {
            return null;
        }

        $src = $item['engagement'];
        if (!is_array($src)) {
            return null;
        }

        return [
            'level'  => $src['level'] ?? null,
            'score'  => isset($src['score']) ? (int)$src['score'] : null,
            'calculated_at' => $src['calculated_at'] ?? null,
            'number_of_days_since_last_email' =>
                isset($src['number_of_days_since_last_email'])
                    ? (int)$src['number_of_days_since_last_email']
                    : null,
        ];
    }

    private function formatMetrics(array $item): ?array
    {
        $src = $item['metrics']['email'] ?? null;

        if (!is_array($src)) {
            return null;
        }

        return [
            'email' => [
                'last_email_sent_at' => $src['last_email_sent_at'] ?? null,
                'last_opened_at'     => $src['last_opened_at'] ?? null,
                'last_clicked_at'    => $src['last_clicked_at'] ?? null,
                'is_hardbounced'     => $src['is_hardbounced'] ?? null,
                'softbounces_in_row' =>
                    isset($src['softbounces_in_row']) ? (int)$src['softbounces_in_row'] : null,
            ]
        ];
    }

    private function formatContactLists(array $item): array
    {
        return is_array($item['contactlists'] ?? null)
            ? $item['contactlists']
            : [];
    }

}
