<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

$date_now = CMbDT::date();
$dateMin  = CMbDT::date("first day of this month", $date_now);
$dateMax  = $date_now;
CView::checkin();

// Chargement de l'utilisateur courant
// et du praticien selectionné
$user       = CMediusers::get();
$prat       = new CMediusers();
$praticiens = $user->loadPraticiens(PERM_READ);

//chargement de la liste des cabinets
$cabinets = CMediusers::loadFonctions(PERM_EDIT, null, "cabinet");

$cabinet = null;

if ($user->isPraticien()) {
  $prat = $user;
}
else {
  $cabinet = $user->function_id;
}

$filter            = new CConsultation();
$filter->_date_min = $dateMin;
$filter->_date_max = $dateMax;

// smarty
$smarty = new CSmartyDP();
$smarty->assign("filter"    , $filter);
$smarty->assign("prat"      , $prat);
$smarty->assign("cabinet"   , $cabinet);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("cabinets"  , $cabinets);
$smarty->display("vw_list_patients.tpl");
