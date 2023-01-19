<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Exception;

use Ox\Core\Kernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationException extends HttpException
{
    /**
     * @inheritDoc
     */
    public function __construct(string $message = null, array $headers = [], $code = 0)
    {
        parent::__construct(Response::HTTP_UNAUTHORIZED, $message, $headers, $code);
    }
}
