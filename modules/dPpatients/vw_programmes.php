<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CProgrammeClinique;
use Ox\Mediboard\Patients\CRegleAlertePatient;

CCanDo::checkEdit();

$selected_year = CView::get("year", "num");
CView::checkin();

$user = CMediusers::get();

$ds    = CSQLDataSource::get("std");
$ljoin = [
  "users_mediboard" => "programme_clinique.coordinateur_id = users_mediboard.user_id"
];

$where = [
  "users_mediboard.function_id" => $ds->prepare("= ?", $user->function_id)
];

$programme  = new CProgrammeClinique();
$order      = "nom ASC";
$programmes = $programme->loadList($where, $order, null, null, $ljoin);

$years         = [];
$filtered_prog = [];

CStoredObject::massLoadBackRefs($programmes, "inclusions_programme");
foreach ($programmes as $_programme) {
  $_programme->loadRefMedecin();
  $_programme->countPatients();
  $_programme->getDateFirstLastInclusion();
  $_programme->loadRefsInclusionsProgramme();

  // Filter by selected year
  $first_inclusion = new DateTime($_programme->_date_first_inclusion);
  if (!$selected_year || $selected_year === 0 || ($selected_year && $first_inclusion->format('Y') == $selected_year)) {
    $filtered_prog[] = $_programme;
  }

  // Get years for the select filter
  foreach ($_programme->_refs_inclusions_programme as $_inclusion) {
    if ($_inclusion->date_debut) {
      $date_debut = new DateTime($_inclusion->date_debut);
      $years[]    = $date_debut->format("Y");
    }
  }
  $years = array_unique($years, SORT_NUMERIC);
  rsort($years);
}

// Load rules
$regle  = new CRegleAlertePatient();
$regles = $regle->loadGroupList(null, "name");
foreach ($regles as $_regle) {
  $_regle->loadRefsUsers();
}

$smarty = new CSmartyDP();

$smarty->assign("programmes", $filtered_prog);
$smarty->assign("years", $years);
$smarty->assign("selected_year", $selected_year);

if (!$selected_year && $selected_year !== 0) {
  $smarty->assign("regles", $regles);
  $smarty->display("vw_programmes");
}
else {
  $smarty->display("inc_vw_programmes");
}
