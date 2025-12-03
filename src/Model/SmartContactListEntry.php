<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContactListEntry
 *
 * Reprezentuje vazbu kontaktu na konkrétní seznam
 * v rámci SmartEmailing API. Obsahuje ID seznamu,
 * stav, datum přidání a datum poslední aktualizace.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
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
