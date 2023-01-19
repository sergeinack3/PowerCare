<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

// Bornes de date des statistiques
$date_min = CValue::get("_date_min", CMbDT::date("-2 MONTHS"));
$date_max = CValue::get("_date_max", CMbDT::date());

// Autres éléments de filtrage
$service_id    = CValue::get("service_id");
$type          = CValue::get("type");
$prat_id       = CValue::get("prat_id");
$func_id       = CValue::get("func_id");
$discipline_id = CValue::get("discipline_id");
$salle_id      = CValue::get("salle_id");
$bloc_id       = CValue::get("bloc_id");
$codes_ccam    = strtoupper(CValue::get("codes_ccam", ""));
$hors_plage    = CValue::get("hors_plage", 1);

$interv = new COperation();
$ds     = $interv->getDS();

$group_id = CGroups::loadCurrent()->_id;
$where    = array(
  "sejour.group_id" => $ds->prepare("= ?", $group_id),
  "operations.date" => $ds->prepare("BETWEEN %1 AND %2", $date_min, $date_max),
);
$ljoin    = array(
  "sejour" => "sejour.sejour_id = operations.sejour_id",
);

if ($service_id) {
  $where["sejour.service_id"] = $ds->prepare("=?", $service_id);
}
if ($type) {
  $where["sejour.type"] = $ds->prepare("=?", $type);
}
if ($prat_id) {
  $where["operations.chir_id"] = $ds->prepare("=?", $prat_id);
}
if ($func_id) {
  $ljoin["users_mediboard"]             = "users_mediboard.user_id = operations.chir_id";
  $where["users_mediboard.function_id"] = $ds->prepare("=?", $func_id);
}
if ($discipline_id) {
  $ljoin["users_mediboard"]               = "users_mediboard.user_id = operations.chir_id";
  $where["users_mediboard.discipline_id"] = $ds->prepare("=?", $discipline_id);
}
if ($salle_id) {
  $where["operations.salle_id"] = $ds->prepare("=?", $salle_id);
}
if ($bloc_id) {
  $ljoin["sallesbloc"]         = "sallesbloc.salle_id = operations.salle_id";
  $where["sallesbloc.bloc_id"] = $ds->prepare("=?", $bloc_id);
}
if (!$hors_plage) {
  $where["operations.plageop_id"] = "IS NOT NULL";
}
if ($codes_ccam) {
  $where["operations.codes_ccam"] = $ds->prepare("LIKE %", "%$codes_ccam%");
}

/** @var COperation[] $interventions */
$interventions = $interv->loadList($where, null, null, "operation_id", $ljoin);

// Chargements de masse
$sejours = CMbObject::massLoadFwdRef($interventions, "sejour_id");
CMbObject::massLoadFwdRef($sejours, "patient_id");
CMbObject::massLoadFwdRef($sejours, "praticien_id");

CMbObject::massLoadFwdRef($interventions, "chir_id");

$columns = array(
  "IPP",
  "Nom",
  "Nom naissance",
  "Prénom",
  "Date naissance",
  "Sexe",

  "Date intervention",
  "Libellé intervention",
  "Chirurgien nom",
  "Chirurgien prénom",

  "NDA",
  "Praticien nom",
  "Praticien prénom",
  "Date entrée",
  "Date sortie",
);

$csv = new CCSVFile();
$csv->writeLine($columns);

foreach ($interventions as $_intervention) {
  $_sejour    = $_intervention->loadRefSejour();
  $_patient   = $_sejour->loadRefPatient();
  $_praticien = $_sejour->loadRefPraticien();
  $_chir      = $_intervention->loadRefChir();

  $_patient->loadIPP();
  $_sejour->loadNDA();

  $row = array(
    // Patient
    $_patient->_IPP,
    $_patient->nom,
    $_patient->nom_jeune_fille,
    $_patient->prenom,
    $_patient->naissance,
    $_patient->sexe,

    // Intervention
    $_intervention->date,
    $_intervention->libelle ?: $_intervention->codes_ccam,
    $_chir->_user_last_name,
    $_chir->_user_first_name,

    // Séjour
    $_sejour->_NDA,
    $_praticien->_user_last_name,
    $_praticien->_user_first_name,
    $_sejour->entree,
    $_sejour->sortie,
  );

  $csv->writeLine($row);
}

$csv->stream("Interventions $date_min - $date_max", true);
