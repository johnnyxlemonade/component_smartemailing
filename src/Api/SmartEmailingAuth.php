<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * SmartEmailingAuth
 *
 * Uchovává přihlašovací údaje (user + token) pro přístup
 * k SmartEmailing API. Immutable objekt — hodnoty nelze měnit
 * po vytvoření.
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
