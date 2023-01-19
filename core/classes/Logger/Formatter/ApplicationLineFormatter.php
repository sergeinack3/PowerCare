<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * Formatter for Application logs. Change the line and date format.
 */
class ApplicationLineFormatter extends LineFormatter {

    public const SIMPLE_FORMAT = "[%datetime%] [%level_name%] %message% [context:%context%] [extra:%extra%]\n";
    public const SIMPLE_DATE =  "Y-m-d H:i:s.u";
}
