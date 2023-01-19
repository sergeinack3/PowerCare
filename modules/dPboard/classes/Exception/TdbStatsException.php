<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Exception;

use Exception;

class TdbStatsException extends Exception
{
    /**
     * @param string $view
     *
     * @return static
     */
    public static function viewNotFound(string $view): self
    {
        return new static("Unknown stat view '$view'");
    }
}
