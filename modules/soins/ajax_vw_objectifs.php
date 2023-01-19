<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id               = CView::get("sejour_id", "ref class|CSejour", true);
$dossier_addictologie_id = CView::get("dossier_addictologie_id", "ref class|CDossierAddictologie");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsObjectifsSoins();
$sejour->countObjectifsSoins();
$sejour->countObjectifsDelayWeek();

$users         = CStoredObject::massLoadFwdRef($sejour->_ref_objectifs_soins, "user_id");
$users_cloture = CStoredObject::massLoadFwdRef($sejour->_ref_objectifs_soins, "cloture_user_id");
$cibles        = CStoredObject::massLoadBackRefs($sejour->_ref_objectifs_soins, "cibles");
CStoredObject::massLoadFwdRef($users, "function_id");
CStoredObject::massLoadFwdRef($users_cloture, "function_id");
CStoredObject::massLoadFwdRef($cibles, "object_id");
CStoredObject::massLoadBackRefs($sejour->_ref_objectifs_soins, "reevaluations", "date");
CStoredObject::massLoadFwdRef($sejour->_ref_objectifs_soins, "objectif_soin_categorie_id");

foreach ($sejour->_ref_objectifs_soins as $_objectif) {
  // Chargement des utilisateurs et des fonctions associées
  $_objectif->loadRefUser()->loadRefFunction();
  $_objectif->loadRefClotureUser()->loadRefFunction();

  // Chargement des cibles liées à l'objectif
  $_objectif->loadRefsCible();
  foreach ($_objectif->_ref_cibles as $_cible) {
    $_cible->loadTargetObject();
  }
  $_objectif->loadRefsReevaluations();
  $_objectif->loadRefCategorie();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("dossier_addictologie_id", $dossier_addictologie_id);
$smarty->display("inc_vw_objectifs");

