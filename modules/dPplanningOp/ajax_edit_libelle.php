<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CLibelleOp;

CCanDo::checkEdit();
$libelle_id = CValue::get("libelle_id");

$libelle = new CLibelleOp();
$libelle->load($libelle_id);

if (!$libelle->_id) {
  $libelle->group_id = CGroups::loadCurrent()->_id;
}

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("libelle",  $libelle);

$smarty->display("vw_edit_libelle");
