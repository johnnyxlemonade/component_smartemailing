# Lemonade SmartEmailing API Client

![License](https://img.shields.io/badge/license-MIT-green)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)
![Packagist Version](https://img.shields.io/packagist/v/lemonade/component-smartemailing)

**Lemonade SmartEmailing API Client** is a fully typed PHP 8.1+ wrapper for the SmartEmailing v3 REST API.  
It provides high-level models, formatting helpers, strict typing, and a clean architecture that transforms raw API responses into structured domain objects.

## Features

- PHP 8.1+
- Typed data models (contacts, lists, metadata, metrics, engagement)
- Unified formatting layer converting raw API responses into structured arrays
- High-level client API (`SmartEmailingClient`)
- Supports:
    - retrieving lists and contacts
    - validating credentials
    - adding/updating contacts
    - deleting contacts
    - listing contacts by list
- Compatible with PHPStan (strict mode)
- Zero external dependencies besides Guzzle

## Installation

Use Composer:

```bash
composer require lemonade/component_smartemailing
```

## Quick Start

### Initialize the API client

```php
use Lemonade\SmartEmailing\Api\SmartEmailingApi;
use Lemonade\SmartEmailing\SmartEmailingClient;

$api = new SmartEmailingApi(
    username: 'YOUR_SMARTEMAILING_LOGIN',
    apiKey:   'YOUR_SMARTEMAILING_API_KEY'
);

$client = new SmartEmailingClient($api);
```

## Validate Credentials

```php
$response = $client->checkLogin();

if ($response->success) {
    echo "API login OK";
} else {
    echo "Login failed: " . $response->message;
}
```

## Retrieve Lists

```php
$lists = $client->getLists();

foreach ($lists as $list) {
    echo $list->getId() . " - " . $list->getName();
}
```

## Retrieve Contacts

```php
$contacts = $client->getContacts();

foreach ($contacts as $contact) {
    echo $contact->getEmail();
}
```

## Add or Update Contact

```php
$response = $client->addOrUpdate(
    email: 'john@example.com',
    listId: 2,
    fields: [
        'name' => 'John',
        'surname' => 'Doe',
        'language' => 'cs_CZ'
    ]
);

if ($response->success) {
    echo "Contact saved.";
}
```

## Get Contacts from List

```php
$listContacts = $client->getContactsByList(2);

foreach ($listContacts as $c) {
    echo $c->getEmail();
}
```

## Delete Contact

```php
$client->removeFromList(123);
```

## License
MIT License © Lemonade Framework
