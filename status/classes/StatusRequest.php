<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use Symfony\Component\HttpFoundation\Request;

class StatusRequest
{
    /**
     * To preserve routes prefix : status, we have to update script name
     * @return Request
     */
    public static function create(): Request
    {
        $request = Request::createFromGlobals();
        $request_install = $request->duplicate(
            null, // query
            null, // request
            null, // attributes
            null, // cookies
            null, // files
            array_merge(
                $request->server->all(),
                [
                    'SCRIPT_NAME' => str_replace('status/', '', $_SERVER['SCRIPT_NAME']),
                ]
            )
        );

        return $request_install;
    }
}
