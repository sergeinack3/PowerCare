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
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$evenement_ssr_id = CView::get("evenement_ssr_id", "ref class|CEvenementSSR", true);
CView::checkin();

$evenement = new CEvenementSSR();
$evenement->load($evenement_ssr_id);
$element_prescription = $evenement->loadRefPrescriptionLineElement();
$evts_patient         = $evenement->loadRefsEvenementsSeance();
foreach ($evts_patient as $_evt_patient) {
  $_evt_patient->loadRefSejour()->loadRefPatient();
  if (!$element_prescription->_id) {
    $element_prescription = $_evt_patient->loadRefPrescriptionLineElement()->_ref_element_prescription;
  }
}
$evenement->_ref_prescription_line_element->_ref_element_prescription = $element_prescription;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement", $evenement);
$smarty->display("vw_gestion_patients_collectifs");
