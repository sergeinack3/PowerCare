<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

// Liste des patientes qui ont :
// - un séjour aux urgences
// - une affectation en maternité
// - une grossesse en cours

$sejour = new CSejour();

$where = [
  "sejour.type"   => CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence()),
  "sejour.sortie" => "> '" . CMbDT::dateTime() . "'",
  "sejour.annule" => "= '0'",
  "patients.sexe" => "= 'f'"
];

$ljoin = [
  "patients" => "patients.patient_id = sejour.patient_id"
];

$sejours = $sejour->loadList($where, null, null, null, $ljoin, 'sortie');

CSejour::massLoadCurrAffectation($sejours);

foreach ($sejours as $_sejour) {
  if (!$_sejour->loadRefPatient()->loadLastGrossesse()->_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  if (!$_sejour->_ref_curr_affectation->loadRefService()->obstetrique) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  $_sejour->loadRefsConsultations();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('sejours', $sejours);

$smarty->display("inc_avis_maternite");
