<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkRead();

$plage_id = CValue::get("plage_id");

$object = new CPlageOp();
$object->load($plage_id);
$object->loadRefsNotes();

$object->loadRefChir()->loadRefFunction();
$object->loadRefAnesth()->loadRefFunction();
$object->loadRefSpec();

$object->loadRefsOperations();
$object->loadRefSalle();

foreach ($object->_ref_operations as $_op) {
  $_op->loadRefPatient()->loadRefPhotoIdentite();
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->display("inc_vw_plageop.tpl");