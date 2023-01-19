<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

class Encoding
{
    public const NONE       = 'none';
    public const UTF_8      = 'UTF-8';
    public const ISO_8859_1 = 'ISO-8859-1';
    public const ENCODINGS  = [
        self::NONE,
        self::UTF_8,
        self::ISO_8859_1,
    ];

    public static function isAValidEncoding(string $value): bool
    {
        return in_array($value, self::ENCODINGS);
    }

    public static function isNoneOrUTF8(string $value): bool
    {
        return ($value === self::UTF_8 || $value === self::NONE);
    }
}
