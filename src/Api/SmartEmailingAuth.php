<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * Class SmartEmailingAuth
 *
 * Datový objekt uchovávající přihlašovací údaje pro SmartEmailing API.
 * Obsahuje uživatelské jméno a token ve formě immutable hodnot.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Auth
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingAuth
{
    public function __construct(
        private readonly string $user,
        private readonly string $token
    ) {}

    public function getUser(): string
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
