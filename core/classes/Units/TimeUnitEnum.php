<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Units;

use MyCLabs\Enum\Enum;

/**
 * Time unit
 * Todo: Replace by Enum in PHP 8.1
 *
 * @method static static NANOSECONDS()
 * @method static static MICROSECONDS()
 * @method static static MILLISECONDS()
 * @method static static SECONDS()
 * @method static static MINUTES()
 * @method static static HOURS()
 * @method static static DAYS()
 */
class TimeUnitEnum extends Enum
{
    private const NANOSECONDS  = 'nanos';
    private const MICROSECONDS = 'micros';
    private const MILLISECONDS = 'ms';
    private const SECONDS      = 's';
    private const MINUTES      = 'm';
    private const HOURS        = 'h';
    private const DAYS         = 'd';
}
