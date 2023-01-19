<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::check();

$idex_value = CView::get("id400", "str");
$object_id  = CView::get("object_id", "str");

CView::checkin();

$admission_active = CModule::getActive("dPadmissions");
$admin_admission  = $admission_active && $admission_active->canAdmin();

$sip_active = CModule::getActive("sip");

$group_id = CGroups::loadCurrent()->_id;

$idexs   = array();
$idex_id = null;

$idex            = new CIdSante400();
$idex->id400     = $idex_value;
$idex->object_id = $object_id;

if ($idex->loadMatchingObject()) {
  $filter               = new CIdSante400();
  $filter->object_class = $idex->object_class;
  $filter->object_id    = $idex->object_id;

  $filter->tag = CSejour::getTagNDA($group_id);
  $idexs       = $filter->loadMatchingList();

  $filter->tag = CSejour::getTagNDA($group_id, "tag_dossier_trash");
  $idexs       += $filter->loadMatchingList();

  $filter->tag = CSejour::getTagNDA($group_id, "tag_dossier_cancel");
  $idexs       += $filter->loadMatchingList();

  $filter->tag = CSejour::getTagNDA($group_id, "tag_dossier_pa");
  $idexs       += $filter->loadMatchingList();

  // Chargement de l'objet afin de récupérer l'id400 associé (le latest)
  $object = new $filter->object_class();
  $object->load($filter->object_id);
  $object->loadNDA($group_id);

  foreach ($idexs as $key => $_idex) {
    $_idex->_ref_object = $object;
    $_idex->getSpecialType();
    if (!$idex_id && $_idex->id400 == $object->_NDA) {
      $idex_id = $_idex->_id;
    }
  }

  ksort($idexs);
}

$smarty = new CSmartyDP();
$smarty->assign("admin_admission", $admin_admission);
$smarty->assign("idexs", $idexs);
$smarty->assign("idex_id", $idex_id);
$smarty->assign("object_id", $object_id);
$smarty->assign("sip_active", $sip_active);
if ($idex->_id) {
  $smarty->assign("patient_id", $object->patient_id);
}

$smarty->display("inc_list_show_id400.tpl");
