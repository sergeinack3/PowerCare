<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger;

use Monolog\Logger;
use Psr\Log\InvalidArgumentException;

/**
 * Facade of Monolog/Logger Const & color mapping
 */
final class LoggerLevels
{
    public const        LEVEL_DEBUG     = 100;
    public const        LEVEL_INFO      = 200;
    public const        LEVEL_NOTICE    = 250;
    public const        LEVEL_WARNING   = 300;
    public const        LEVEL_ERROR     = 400;
    public const        LEVEL_CRITICAL  = 500;
    public const        LEVEL_ALERT     = 550;
    public const        LEVEL_EMERGENCY = 600;

    public const LEVELS_COLORS = [
       'DEBUG'     => "DimGray",
       'INFO'      => "black",
       'NOTICE'    => "DodgerBlue",
       'WARNING'   => "orange",
       'ERROR'     => "DarkOrange",
       'CRITICAL'  => "Tomato",
       'ALERT'     => "red",
       'EMERGENCY' => "DarkViolet",
    ];

    public static function getLevelName(int $level): string
    {
        return Logger::getLevelName($level);
    }

    public static function getLevelColor(string $level_name): string
    {
        if (!isset(self::LEVELS_COLORS[$level_name])) {
            throw new InvalidArgumentException('Level "' . $level_name . '" is not defined');
        }

        return self::LEVELS_COLORS[$level_name];
    }
}
