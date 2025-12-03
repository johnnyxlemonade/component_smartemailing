<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * Class SmartEmailingSchema
 *
 * Definice konstant SmartEmailing API: endpointy, metody,
 * základní URL, verze API a pomocné utility pro generování
 * cest a filtrování schémat. Slouží jako centrální konfigurace
 * pro komunikaci klienta i API wrapperu.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Schema
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingSchema
{
    // ============================================================
    // API base settings
    // ============================================================

    public const APP_BASE_URI = 'https://app.smartemailing.cz';
    public const APP_VERSION  = 'api/v3';

    // ============================================================
    // Endpoints
    // ============================================================

    public const ACTION_PING              = 'ping';
    public const ACTION_CHECK_CREDENTIALS = 'check-credentials';
    public const ACTION_CONTACTLISTS      = 'contactlists';
    public const ACTION_CONTACT_FORGET    = 'contacts/forget';
    public const ACTION_CONTACTS          = 'contacts';
    public const ACTION_IMPORT            = 'import';

    // ============================================================
    // HTTP methods
    // ============================================================

    public const METHOD_GET    = 'GET';
    public const METHOD_POST   = 'POST';
    public const METHOD_PUT    = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    // ============================================================
    // Contact filters
    // ============================================================

    public const LIST_ALL          = 'contacts';
    public const LIST_CONFIRMED    = 'confirmed';
    public const LIST_UNSUBSCRIBED = 'unsubscribed';

    // ============================================================
    // Helpers
    // ============================================================

    /**
     * Vrací API URL ve formátu "api/v3/{endpoint}".
     * Runtime chování zachováno 1:1.
     */
    public static function parseUrl(?string $url = null): string
    {
        return sprintf('%s/%s', self::APP_VERSION, $url ?? '');
    }

    /**
     * Vrací seznam všech konstant, nebo jen těch s prefixem.
     * Zachována původní logika včetně strpos().
     */
    public static function getSchema(?string $prefix = null): array
    {
        $refl = new \ReflectionClass(self::class);
        $result = [];

        $needle = $prefix ?? '';

        foreach ($refl->getConstants() as $key => $value) {
            if ($needle !== '') {
                if (strpos($key, $needle) !== false) {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
