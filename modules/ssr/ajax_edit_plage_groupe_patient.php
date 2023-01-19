<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Mediboard\Ssr\CPlateauTechnique;

CCanDo::checkEdit();
$plage_groupe_patient_id      = CView::get("plage_groupe_patient_id", "ref class|CPlageGroupePatient");
$categorie_groupe_patient_id  = CView::get("categorie_groupe_patient_id", "ref class|CCategorieGroupePatient", true);
$current_mod                  = CView::get("current_mod", "str", true);
CView::checkin();

$plage_groupe_patient = CPlageGroupePatient::findOrNew($plage_groupe_patient_id);
$group                = CGroups::loadCurrent();

$plage_groupe_patient->loadRefElementsPresciption();
if (! $plage_groupe_patient->_id) {
  $plage_groupe_patient->categorie_groupe_patient_id = $categorie_groupe_patient_id;
}

// Load all the group categories
$categorie_groupe              = new CCategorieGroupePatient();
$categorie_groupe->type        = $current_mod;
$categorie_groupe->group_id    = $group->_id;
$categories_groupe             = $categorie_groupe->loadMatchingList("nom ASC");

// Loading all trays and associated equipment and technicians
$where             = array();
$where["type"]     = " = '$current_mod' OR type IS NULL";

$plateau_technique = new CPlateauTechnique();
$plateaux          = $plateau_technique->loadGroupList($where);

CMbObject::massLoadBackRefs($plateaux, "equipements", "nom ASC");

foreach ($plateaux as $_plateau) {
  $_plateau->loadRefsEquipements();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("plage_groupe_patient", $plage_groupe_patient);
$smarty->assign("categories_groupe"   , $categories_groupe);
$smarty->assign("plateaux"            , $plateaux);
$smarty->display("inc_edit_plage_groupe_patient");
