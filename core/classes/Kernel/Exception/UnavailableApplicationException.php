<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Exception;

use Ox\Core\CApp;
use Symfony\Component\HttpFoundation\Response;

/**
 * The application is unavailable because the offline_mode is active or the database is not available.
 *
 * This type of exception is not loggable because the file buffer would remain on the server and never be send
 * to the database.
 */
class UnavailableApplicationException extends HttpException
{
    protected $is_loggable = false;

    public static function applicationIsDisabledBecauseOfMaintenance(): self
    {
        // Cannot use locales because they might not be loaded
        return new self(
            Response::HTTP_SERVICE_UNAVAILABLE,
            CApp::MSG_OFFLINE_MAINTENANCE,
            ["Retry-After" => "300"]
        );
    }

    public static function databaseIsNotAccessible(): self
    {
        // Cannot use locales because they might not be loaded
        return new self(
            Response::HTTP_SERVICE_UNAVAILABLE,
            CApp::MSG_OFFLINE_DATABASE
        );
    }
}
