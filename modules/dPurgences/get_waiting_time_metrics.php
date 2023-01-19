<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


// Normal GET
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Urgences\CRPU;

$h = (int)CValue::get("h", 24);

$rpu = new CRPU();

$group = CGroups::loadCurrent();

$code_etab = CIdSante400::getValueFor($group, "export_timing_urgences") ?: "-";

$date_min = CMbDT::dateTime("-$h HOURS");

$where = array(
  "sejour.group_id" => "= '$group->_id'",
  "sejour.entree"   => ">= '$date_min'"
);

$ljoin = array(
  "sejour" => "sejour.sejour_id = rpu.sejour_id",
);

$rpus = $rpu->loadList($where, null, 200, null, $ljoin);

$csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);

$columns = array(
  "age",
  "ccmu",
  "dateArrivée",
  "datePriseEnChargeIAO",
  "datePriseEnChargeMed",
  "dateSortie",
  "libelleEtablissement",
  "typeDeMotif",
  null,
  "orientation",
  "typeDeCircuit",
  "tempsDePassage",
  "numeroDossier",
);

$csv->writeLine($columns);

$ccmu = array(
  "1" => "I",
  "2" => "II",
  "3" => "III",
  "4" => "IV",
  "5" => "V",
  "D" => "D",
  "P" => "P",
);

/**
 * Convertit une date au format voulu
 *
 * @param string $datetime Date à convertir
 *
 * @return null|string
 */
function formatDT($datetime) {
  if (!$datetime) {
    return null;
  }

  return CMbDT::format($datetime, "%Y/%m/%d %H:%M");
}

foreach ($rpus as $_rpu) {
  /** @var CSejour $_sejour */
  $_sejour      = $_rpu->loadRefSejour();
  $_consult     = $_rpu->loadRefConsult();
  $_patient     = $_sejour->loadRefPatient();
  $_sfmu        = $_rpu->loadRefMotifSFMU();
  $nda          = $_sejour->loadNDA();
  $tempsPassage = CMbDT::format(CMbDT::durationTime($_rpu->_entree, $_rpu->_sortie), "%Hh %Mm");

  $type_motif = ($_sfmu->categorie == "Traumatologie") ? "TRAUMATOLOGIE" : "MEDECINE";

  $orientation = "AUTRE";

  if (!$_sejour->DP && !$_rpu->gemsa && !$_rpu->orientation) {
    $orientation = "NON PRIS EN CHARGE";
  }
  elseif ($_sejour->mode_sortie == "normal") {
    $orientation = "RETOUR DOMICILE";
  }
  elseif ($_sejour->mode_sortie == "deces") {
    $orientation = "DECES";
  }
  elseif ($_rpu->gemsa == 1) {
    $orientation = "DECES ANTERIEUR ARRIVEE";
  }
  elseif ($_rpu->gemsa == 3) {
    $orientation = "CONSULTATION";
  }
  elseif ($_sejour->transport_sortie == "fo") {
    $orientation = "POLICE";
  }
  elseif ($_rpu->orientation == "SCAM") {
    $orientation = "CONTRE AVIS";
  }
  elseif ($_rpu->orientation == "SCAM") {
    $orientation = "CONTRE AVIS";
  }
  elseif ($_rpu->orientation == "PSA") {
    $orientation = "PARTI SANS ATTENDRE";
  }
  elseif ($_rpu->orientation == "FUGUE") {
    $orientation = "EVASION";
  }
  elseif ($_sejour->mode_sortie == "mutation") {
    $orientation = "TRANSFERT INTERNE";
  }
  elseif ($_sejour->mode_sortie == "transfert") {
    $orientation = "TRANSFERT EXTERNE";
  }

  $_pec_med = null;

  if ($_consult->_id) {
    $_consult->loadRefPlageConsult();
    $_pec_med = $_consult->_datetime;
  }

  $row = array(
    $_patient->evalAge(),              // "age",
    CMbArray::get($ccmu, $_rpu->ccmu), // "ccmu",
    formatDT($_sejour->entree),        // "dateArrivée",
    formatDT($_rpu->pec_ioa),          // "datePriseEnChargeIAO",
    formatDT($_pec_med),               // "datePriseEnChargeMed",
    formatDT($_sejour->sortie_reelle), // "dateSortie",
    $code_etab,                        // "libelleEtablissement",
    $type_motif,                       // "typeDeMotif",
    null,                              // "colonne I",
    $orientation,                      // "orientation",
    null,                              // "typeDeCircuit",
    $tempsPassage,                     // "tempsDePassage",
    $_sejour->_NDA,                    // "numeroDossier",
  );

  $csv->writeLine($row);
}

$csv->stream("realtimestate");