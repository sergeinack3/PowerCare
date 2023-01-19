<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use Exception;

class StatusException extends Exception
{
    /**
     * StatusException constructor.
     *
     * @param string $message
     * @param int    $code
     */
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}
