<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Units;

use MyCLabs\Enum\Enum;

/**
 * Different size units for bytes
 * Todo: Replace by Enum in PHP 8.1
 *
 * @method static static PETABYTES()
 * @method static static TERABYTES()
 * @method static static GIGABYTES()
 * @method static static MEGABYTES()
 * @method static static KILOBYTES()
 * @method static static BYTES()
 */
class ByteSizeUnitEnum extends Enum
{
    private const PETABYTES = 'pb';
    private const TERABYTES = 'tb';
    private const GIGABYTES = 'gb';
    private const MEGABYTES = 'mb';
    private const KILOBYTES = 'kb';
    private const BYTES     = 'b';
}
