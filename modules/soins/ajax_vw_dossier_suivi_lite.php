<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CTransmissionMedicale;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$group                        = CGroups::loadCurrent();
$condensee_trans_limit_sejour = CAppUI::conf("soins Other condensee_trans_limit_sejour", $group->_guid);

// Chargement des observations d'importance haute de moins de 7 jours
$observation                                     = new CObservationMedicale();
$where                                           = array();
$where["observation_medicale.degre"]             = " = 'high'";
$where["observation_medicale.sejour_id"]         = " = '$sejour_id'";
$where["observation_medicale.cancellation_date"] = " IS NULL";

if (!$condensee_trans_limit_sejour) {
  $where["observation_medicale.date"] = " >= '" . CMbDT::dateTime("- 7 DAYS") . "'";
}
/* @var CObservationMedicale[] $observations */
$observations = $observation->loadList($where, null, null, "observation_medicale.observation_medicale_id");

// Chargement des transmissions d'importance haute ou des macrocibles de moins de 7 jours
$where                                            = array();
$where["transmission_medicale.sejour_id"]         = " = '$sejour_id'";
$where["transmission_medicale.cancellation_date"] = " IS NULL";
$where[]                                          = "transmission_medicale.degre = 'high' OR category_prescription.cible_importante = '1'";

if (CAppUI::conf("soins synthese transmission_date_limit", $group->_guid)) {
  $where[] = "transmission_medicale.date_max >= '" . CMbDT::dateTime() . "' OR transmission_medicale.date_max IS NULL";
}

$ljoin                          = array();
$ljoin["category_prescription"] = "transmission_medicale.object_id = category_prescription.category_prescription_id
                                  AND transmission_medicale.object_class = 'CCategoryPrescription'";
if (!$condensee_trans_limit_sejour) {
  $where["transmission_medicale.date"] = " >= '" . CMbDT::dateTime("- 7 DAYS") . "'";
}

$transmission = new CTransmissionMedicale();
/* @var CTransmissionMedicale[] $transmissions */
$transmissions = $transmission->loadList($where, null, null, "transmission_medicale.transmission_medicale_id", $ljoin);

$suivi = array();

CStoredObject::massLoadFwdRef($observations, "sejour_id");
$users = CStoredObject::massLoadFwdRef($observations, "user_id");
CStoredObject::massLoadFwdRef($users, "function_id");

foreach ($observations as $_observation) {
  $_observation->loadRefSejour();
  $_observation->loadRefUser()->loadRefFunction();
  $suivi[$_observation->date . $_observation->_id] = $_observation;
}

CStoredObject::massLoadFwdRef($transmissions, "sejour_id");
$users = CStoredObject::massLoadFwdRef($transmissions, "user_id");
CStoredObject::massLoadFwdRef($users, "function_id");

foreach ($transmissions as $_transmission) {
  $_transmission->loadRefSejour();
  $_transmission->loadRefUser()->loadRefFunction();
  $suivi[$_transmission->date . $_transmission->_guid] = $_transmission;
}

krsort($suivi);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("suivi", $suivi);
$smarty->assign("readonly", true);
$smarty->display("inc_vw_dossier_suivi_lite");
