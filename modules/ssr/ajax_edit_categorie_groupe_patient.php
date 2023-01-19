<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;

CCanDo::checkEdit();
$categorie_groupe_patient_id = CView::get("categorie_groupe_patient_id", "ref class|CCategorieGroupePatient");
$current_mod                 = CView::get("current_mod", "str", true);
CView::checkin();

$categorie_groupe_patient = CCategorieGroupePatient::findOrNew($categorie_groupe_patient_id);
$group                    = CGroups::loadCurrent();

if (!$categorie_groupe_patient->_id) {
  $categorie_groupe_patient->group_id    = $group->_id;
  $categorie_groupe_patient->type        = $current_mod;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("categorie_groupe_patient", $categorie_groupe_patient);
$smarty->display("inc_edit_categorie_groupe_patient");
