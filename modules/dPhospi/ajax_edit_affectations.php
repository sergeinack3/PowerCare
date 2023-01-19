<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadRefPraticien();

$affectations = $sejour->loadRefsAffectations();

CStoredObject::massLoadBackRefs($affectations, "movements", "start_of_movement DESC, last_update DESC");
CStoredObject::massLoadFwdRef($affectations, "service_id");
CStoredObject::massLoadFwdRef($affectations, "uf_soins_id");
CStoredObject::massLoadFwdRef($affectations, "uf_medicale_id");
CStoredObject::massLoadFwdRef($affectations, "uf_hebergement_id");
CStoredObject::massLoadFwdRef($affectations, "praticien_id");
CAffectation::massUpdateView($affectations);

foreach ($affectations as $_affectation) {
  $_affectation->loadRefsMovements();
  $_affectation->loadRefService();
  $_affectation->loadRefPraticien();
  $_affectation->loadRefUFSoins();
  $_affectation->loadRefUFMedicale();
  $_affectation->loadRefUFHebergement();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);

$smarty->display("inc_edit_affectations");
