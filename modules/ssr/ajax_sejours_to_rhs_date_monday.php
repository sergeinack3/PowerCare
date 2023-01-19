<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$rhs_date_monday = CView::get("rhs_date_monday", "date");
$services_ids    = CView::get("services_ids", "str");
$services_ids    = CService::getServicesIdsPref($services_ids);

$show_np = false;
if (array_key_exists("NP", $services_ids)) {
  unset($services_ids["NP"]);
  $show_np = true;
}
CView::checkin();

$group_id                 = CGroups::loadCurrent()->_id;
$date                     = CMbDT::date();
$rhs                      = new CRHS();
$join['sejour']           = "sejour.sejour_id = rhs.sejour_id";
$join['patients']         = "patients.patient_id = sejour.patient_id";
$where['sejour.annule']   = " = '0'";
$where['sejour.group_id'] = " = '$group_id'";
$where[]                  = ($show_np || !count($services_ids) ? "sejour.service_id IS NULL OR " : "")
  . "sejour.service_id " . CSQLDataSource::prepareIn($services_ids);
$where['date_monday']     = " = '$rhs_date_monday'";
$order                    = "nom, prenom";

/** @var CRHS[] $sejours_rhs */
$sejours_rhs = $rhs->loadList($where, $order, null, null, $join);
foreach ($sejours_rhs as $_rhs) {
  $_rhs->loadRefsNotes();
  $sejour = $_rhs->loadRefSejour();
  $sejour->_ref_patient->loadIPP();
}

$where['rhs.facture']    = " = '0'";
$count_sej_rhs_no_charge = $rhs->countList($where, null, $join);

$service  = new CService();
$services = $service->loadListWithPerms(PERM_READ);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours_rhs", $sejours_rhs);
$smarty->assign("count_sej_rhs_no_charge", $count_sej_rhs_no_charge);
$smarty->assign("rhs_date_monday", $rhs_date_monday);
$smarty->assign("read_only", true);
$smarty->assign("services", $services);
$smarty->assign("services_ids", $services_ids);

$smarty->display("inc_vw_rhs_sejour");
