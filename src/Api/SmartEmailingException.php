<?php declare(strict_types=1);

namespace Lemonade\SmartEmailing\Api;

/**
 * Class SmartEmailingException
 *
 * Specifická výjimka pro SmartEmailing API vrstvu.
 * Slouží k odlišení chyb vzniklých při komunikaci se SmartEmailingem
 * od ostatních aplikačních výjimek.
 *
 * @package     Lemonade Framework
 * @subpackage  SmartEmailing
 * @category    Exception
 * @link        https://lemonadeframework.cz/
 * @author      Honza Mudrak <honzamudrak@gmail.com>
 * @license     MIT
 * @since       1.0.0
 */
final class SmartEmailingException extends \Exception
{
}
