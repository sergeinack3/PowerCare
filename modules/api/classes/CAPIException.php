<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\CAppUI;

/**
 * Description
 */
class CAPIException extends Exception {
  /**
   * CAPIException constructor.
   *
   * @param string $message = ''
   * @param int    $code    = 0
   * @param mixed  $_       = null
   */
  public function __construct($message = '', $code = 0, $_ = null) {
    $args    = func_get_args();
    $message = CAppUI::tr($message, array_slice($args, 2));

    parent::__construct($message, $code, null);
  }
}
