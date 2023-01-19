<?php
/**
 * @package Mediboard\Core\Requirements
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Requirements;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;

/**
 * Description
 */
class CRequirementsException extends CMbException {

  const TOO_MUCH_REQUIREMENTS_CLASS = "1";

  /**
   * CConstantException constructor.
   *
   * @param int    $id  of exception
   * @param String $msg optionnal msg
   */
  public function __construct($id, $msg = "") {
    $message = CAppUI::tr("CRequirementsException-" . $id, $msg);
    parent::__construct($message, $id);
  }

}

