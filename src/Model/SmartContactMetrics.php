<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContactMetrics
 *
 * Metriky emailového chování kontaktu v SmartEmailing API.
 * Obsahují čas posledního odeslání, otevření a prokliku emailu,
 * stav hardbounce a počet softbounce v řadě.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
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
