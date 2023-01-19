<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$sejour_id = CValue::getOrSession("sejour_id");

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->loadRefPraticien();

$evenement            = new CEvenementSSR();
$evenement->sejour_id = $sejour_id;
$evenements           = $evenement->loadMatchingList("debut ASC");

$evenements_ssr = [];
/* @var CEvenementSSR[] $evenements */
foreach ($evenements as $_evenement) {
    $_evenement->loadRefPrescriptionLineElement();
    $_evenement->loadRefTherapeute();
    if ($_evenement->type_seance == "collective") {
        $seance = $_evenement->loadRefSeanceCollective();
        $seance->loadRefTherapeute();
        $seance->_ref_prescription_line_element                                    = $_evenement->_ref_prescription_line_element;
        $evenements_ssr[CMbDT::date($seance->debut)][$seance->debut][$seance->_id] = $seance;
    } else {
        $evenements_ssr[CMbDT::date($_evenement->debut)][$_evenement->debut][$_evenement->_id] = $_evenement;
    }
}
foreach ($evenements_ssr as $_evts_ssr_date) {
    foreach ($_evts_ssr_date as $_evts_ssr_time) {
        $order = CMbArray::pluck($_evts_ssr_time, "debut");
        array_multisort($order, SORT_ASC, $_evts_ssr_time);
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("evenements", $evenements_ssr);

$smarty->display("vw_list_events_sejour");
