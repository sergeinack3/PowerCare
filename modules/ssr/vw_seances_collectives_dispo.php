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
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;

global $m;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
// Prescription SSR
$prescription = $sejour->loadRefPrescriptionSejour();

// Chargement des lignes de la prescription
$evts_collectifs = array();
if ($prescription->_id) {
  $elements     = $prescription->loadRefsLinesElement();
  $elements_ids = CMbArray::pluck($elements, "element_prescription_id");
  //Recherches des événements collectifs ayant les bon éléments de prescriptions dans les bornes du séjour
  $ljoin                              = array();
  $ljoin[]                            = "evenement_ssr AS evt_seance ON (evt_seance.seance_collective_id = evenement_ssr.evenement_ssr_id)";
  $ljoin["prescription_line_element"] =
    "evt_seance.prescription_line_element_id = prescription_line_element.prescription_line_element_id";

  $where                                                      = array();
  $where["evt_seance.sejour_id"]                              = " <> '$sejour->_id'";
  $where["evenement_ssr.sejour_id"]                           = " IS NULL";
  $where["evenement_ssr.realise"]                             = " = '0'";
  $where["evenement_ssr.annule"]                              = " = '0'";
  $where["evenement_ssr.debut"]                               = "BETWEEN '" . CMbDT::date($sejour->entree) . " 00:00:00' AND '" . CMbDT::date($sejour->sortie) . " 23:59:59'";
  $where["prescription_line_element.element_prescription_id"] = CSQLDataSource::prepareIn($elements_ids);
  $seance                                                     = new CEvenementSSR();
  $evts_collectifs                                            = $seance->loadList($where, "debut", null, "evenement_ssr_id", $ljoin);

  foreach ($evts_collectifs as $_evt_col) {
    /* @var CEvenementSSR $_evt_col */
    $_evt_col->loadRefsEvenementsSeance();
    foreach ($_evt_col->_ref_evenements_seance as $_evt_seance) {
      $_evt_seance->loadRefSejour();
      if ($_evt_seance->sejour_id == $sejour->_id || $_evt_seance->_ref_sejour->type != $sejour->type) {
        unset($evts_collectifs[$_evt_col->_id]);
        continue;
      }
    }
    $_evt_col->loadRefPrescriptionLineElement()->loadRefElement();
    $_evt_col->loadRefTherapeute()->loadRefFunction();
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("evts_collectifs", $evts_collectifs);
$smarty->assign("sejour", $sejour);
$smarty->display("vw_seances_collectives_dispo");
