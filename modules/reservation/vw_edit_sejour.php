<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// right on reservation
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;

CCanDo::checkRead();

// right on dPplanningOp
$pl_op = CModule::getActive("dPplanningOp");
if ($pl_op->canDo()->edit) {
  CAppUI::requireModuleFile("dPplanningOp", "vw_edit_sejour");
}
