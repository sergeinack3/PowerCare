<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CAppUI;
use Ox\Core\Kernel\Exception\HttpException;

/**
 * Class CEAIException
 */
class CEAIException extends HttpException {

    public const INVALID_SENDER  = 1;
    public const INVALID_DISPATCH  = 2;

    /**
     * CEAIException constructor.
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
            $exploded_path = explode('\\', CEAIException::class);
        } else {
            $exploded_path = explode('\\', get_class($this));
        }

        $class         = end($exploded_path);
        $message       = CAppUI::tr($class . '-' . $code, $args ?? null);
        parent::__construct($status_code, $message, [], $code);
    }
}
