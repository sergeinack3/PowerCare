<?php

/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api\Exception;

use Ox\Core\CAppUI;
use Ox\Core\Kernel\Exception\HttpException;

class CPWAException extends HttpException
{

    public const INVALID_ARGUMENTS  = 1;
    public const NO_PERMISSION      = 2;
    public const INVALID_STORE      = 3;
    public const MISSING_PARAMETERS = 4;
    public const OBJECT_NOT_FOUND   = 5;
    public const INVALID_DELETE     = 6;


    /**
     * CAppFineException constructor.
     *
     * @param int         $code
     * @param int         $status_code
     * @param array|mixed ...$args arguments
     */
    public function __construct(int $code, int $status_code = 400, ...$args)
    {
        if ($args && !is_array($args)) {
            $args = [$args];
        }

        if ($code < 100) {
            $exploded_path = explode('\\', CPWAException::class);
        } else {
            $exploded_path = explode('\\', get_class($this));
        }

        $class         = end($exploded_path);
        $message       = CAppUI::tr($class . '-' . $code, $args ?? null);
        parent::__construct($status_code, $message, [], $code);
    }
}
