<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Model;

/**
 * Class SmartContactEngagement
 *
 * Model reprezentující engagement metriky kontaktu
 * získané ze SmartEmailing API (úroveň, skóre,
 * datum výpočtu a počet dnů od posledního emailu).
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Model
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
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
