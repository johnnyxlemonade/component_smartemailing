<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContactMeta
 *
 * Metadata kontaktu poskytnutá SmartEmailing API.
 * Obsahují UID, verzi záznamu, datum vytvoření,
 * datum aktualizace a zdroj původu.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
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
