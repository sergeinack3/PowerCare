<?php
/**
 * @package Mediboard\\core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Exception;

class CRefCheckerException extends Exception {

  /**
   * CRefCheckerException constructor.
   *
   * @param string   $message
   * @param int      $code
   * @param CRefSpec $ref_spec
   */
  public function __construct($message, $code, CRefSpec $ref_spec) {
    $message .= " in class '{$ref_spec->className}' field '{$ref_spec->fieldName}'";
    parent::__construct($message, $code);
  }
}