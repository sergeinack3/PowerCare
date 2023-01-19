<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends HttpException
{
    /** @var bool */
    protected $is_loggable = false;

    public function __construct($message = null, array $headers = [], $code = 0)
    {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $headers, $code);
    }
}
