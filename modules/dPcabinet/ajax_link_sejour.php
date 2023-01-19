<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$post_redirect = CView::get("post_redirect", "str default|m=cabinet&tab=edit_planning");
$consult_id    = CView::getRefCheckEdit("consult_id", "ref class|CConsultation");

CView::checkin();

$group_id   = CGroups::loadCurrent()->_id;

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPlageConsult();

// Consultations
$date = $consult->_ref_plageconsult->date;
$where = array(
  "patient_id"      => "= '$consult->patient_id'",
);

// La contrainte de date et heure doit se faire en une clause
// (sinon décorelation : on peut exclure des consultations avec heure antérieure et date supérieure)
$where[] = "CONCAT(plageconsult.date, ' ', heure) >= '$date $consult->heure'";

$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);

/** @var CConsultation[] $consults */
$consults = $consult->loadListWithPerms(PERM_READ, $where, null, null, null, $ljoin);
CStoredObject::massLoadFwdRef($consults, "sejour_id");
foreach ($consults as $_consult) {
  $_consult->loadRefPraticien()->loadRefFunction();
  $_consult->loadRefSejour();
}

// Séjours
$where = array(
  "sejour.type"       => "!= 'consult'",
  "sejour.group_id"   => "= '$group_id'",
  "sejour.patient_id" => "= '$consult->patient_id'",
  "sejour.annule"     => "= '0'"
);

$where[] = "'$consult->_date' BETWEEN DATE(entree) AND DATE(sortie)";

/** @var CSejour[] $sejours */
$sejour = new CSejour();
$sejours = $sejour->loadListWithPerms(PERM_READ, $where);
CStoredObject::massLoadFwdRef($sejours, "praticien_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien()->loadRefFunction();
}

$smarty = new CSmartyDP();

$smarty->assign("sejours"      , $sejours);
$smarty->assign("consult"      , $consult);
$smarty->assign("next_consults", $consults);
$smarty->assign("post_redirect", $post_redirect);

$smarty->display("inc_link_sejour.tpl");
