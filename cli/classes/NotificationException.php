<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Exception;

class NotificationException extends Exception {
  /**
   * CliException constructor.
   *
   * @param string    $message   Message of the exception
   * @param int       $code      Code of the exception
   * @param Exception $previous  Previous exception
   * @param string    $log_file  Path of the log file (can be empty)
   * @param bool      $send_mail Send of not a mail
   */
  public function __construct($message, $code = 0, Exception $previous = null, $log_file = '', $send_mail = false) {
    parent::__construct($message, $code, $previous);

    if ($log_file && file_exists($log_file)) {
      file_put_contents($log_file, $message);
    }
  }
}
