<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Exception;

use Ox\Core\CAppUI;
use Ox\Core\Kernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationFailedException extends HttpException
{
    // Do not log failed authentications
    /** @var bool */
    protected $is_loggable = false;

    public static function invalidCredentials(): self
    {
        return new static(Response::HTTP_UNAUTHORIZED, CAppUI::tr('Auth-failed-combination'));
    }
}
