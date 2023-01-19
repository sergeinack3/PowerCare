<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Exception;

use Exception;

class RouteException extends Exception
{
    /**
     * RouteException constructor.
     *
     * @param string $message sprintf compat
     * @param array  $args    arguments
     */
    public function __construct($message = '', ...$args)
    {
        if ($args) {
            $message = sprintf($message, ...$args);
        }
        parent::__construct($message, 0, null);
    }
}
