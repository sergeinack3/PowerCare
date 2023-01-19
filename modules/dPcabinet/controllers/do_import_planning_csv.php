<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CProgressBar;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkEdit();

$file              = CValue::read($_FILES, 'upload_file');
$prat_id           = CView::post("prat_id", "ref class|CMediusers notNull");
$lite              = CView::post("lite", "str default|0");
$upload_identifier = CView::post("upload_identifier", "str");
$force_update      = CView::post("force_update", "str default|0");

CView::checkin();

$prat = CMediusers::get($prat_id);

if (!$lite && (!$prat_id || !$prat->_id) || $file['error']) {
  return;
}

$file_name = $file['tmp_name'];

$fp = fopen($file_name, "r");

$csv     = new CCSVFile($fp, CCSVFile::PROFILE_AUTO);
$columns = $csv->readLine();
$csv->setColumnNames($columns);

$pb = new CProgressBar('import-progress', $csv->countLines());

$tag = "planning-import-" . hash("crc32b", preg_replace('/[^a-z0-9]/i', '', $upload_identifier));

$errors = array();

$line = 1;

$today = CMbDT::date();

if ($lite) {
  $praticiens = array();

  while ($row = $csv->readLine(true)) {
    $row = array_map("trim", $row);

    $pb->adv();

    $line++;

    $_rpps = $row["rpps"];

    if (!$_rpps) {
      $errors[] = array(
        "line" => $line,
        "msg"  => "RPPS manquant",
      );

      continue;
    }

    if (!array_key_exists($_rpps, $praticiens)) {
      $_praticien       = new CMediusers();
      $_praticien->rpps = $_rpps;

      if (!$_praticien->loadMatchingObjectEsc()) {
        $errors[] = array(
          "line" => $line,
          "msg"  => "Praticien non retrouvé par son RPPS ($_rpps)",
        );

        $praticiens[$_rpps] = false;

        continue;
      }

      $praticiens[$_rpps] = $_praticien;
    }

    if (!$praticiens[$_rpps]) {
      $errors[] = array(
        "line" => $line,
        "msg"  => "Praticien non retrouvé par son RPPS ($_rpps)",
      );

      continue;
    }

    $_praticien = $praticiens[$_rpps];

    $_ipp = $row["ipp"];

    if (!$_ipp) {
      $errors[] = array(
        "line" => $line,
        "msg"  => "IPP manquant",
      );

      continue;
    }

    $_patient       = new CPatient();
    $_patient->_IPP = $_ipp;
    $_patient->loadFromIPP();

    if (!$_patient->_id) {
      $errors[] = array(
        "line" => $line,
        "msg"  => "Patient non retrouvé par son IPP ($_ipp)",
      );

      continue;
    }

    $plage          = new CPlageconsult();
    $plage->chir_id = $_praticien->_id;
    $plage->date    = $row["date"];
    $plage->debut   = $row["heure"];
    $plage->fin     = CMbDT::time("+15 MINUTES", $row["heure"]);

    if (!$plage->hasCollisions()) {
      $errors[] = array(
        "line" => $line,
        "msg"  => "Plage non trouvée",
      );

      continue;
    }
    else {
      $plage = reset($plage->_colliding_plages);
    }

    $consult                  = new CConsultation();
    $consult->patient_id      = $_patient->_id;
    $consult->plageconsult_id = $plage->_id;
    $consult->heure           = $row["heure"];
    $consult->loadMatchingObjectEsc();

    $consult->duree  = 1;
    $consult->motif  = $row["motif"];
    $consult->chrono = $plage->date > $today ? CConsultation::PLANIFIE : CConsultation::TERMINE;

    if ($msg = $consult->store()) {
      $errors[] = array(
        "line" => $line,
        "msg"  => $msg,
      );
    }
  }

  $pb->adv();
}
else {
  while ($row = $csv->readLine(true)) {
    $line++;

    $pb->adv();

    // Plage
    $plage_id = $row["plage ID"];

    /** @var CPlageconsult $plage */
    $idex_plage = CIdSante400::getMatch("CPlageconsult", $tag, $plage_id);
    $plage      = $idex_plage->loadTargetObject();

    if (!$plage->_id || $force_update) {
      $plage->chir_id = $prat->_id;
      $plage->date    = $row["plage date"];
      $plage->debut   = $row["plage debut"];
      $plage->fin     = $row["plage fin"];

      if (!$plage->hasCollisions()) {
        $plage->freq    = $row["plage frequence"];
        $plage->libelle = $row["plage libelle"];
        $plage->color   = $row["plage couleur"];

        if ($msg = $plage->store()) {
          $errors[] = array(
            "line" => $line,
            "msg"  => $msg,
          );

          continue;
        }
      }
      else {
        $plage = reset($plage->_colliding_plages);
      }
    }
    else {
      $plage->chir_id = $plage->chir_id ?: $prat->_id;
      $plage->date    = $plage->date ?: $row['plage date'];
      $plage->debut   = $plage->debut ?: $row['plage debut'];
      $plage->fin     = $plage->fin ?: $row['plage fin'];
      $plage->freq    = $plage->freq ?: $row['plage frequence'];
      $plage->libelle = $plage->libelle ?: $row['plage libelle'];
      $plage->color   = $plage->color ?: $row['plage couleur'];

      if ($msg = $plage->store()) {
        $errors[] = array(
          "line" => $line,
          "msg"  => $msg,
        );
      }
    }

    $idex_plage->object_id = $plage->_id;
    $idex_plage->store();

    // Patient
    $patient_id = $row["patient ID"];

    if (!$patient_id) {
      continue;
    }

    /** @var CPatient $patient */
    $idex_patient = CIdSante400::getMatch("CPatient", $tag, $patient_id);
    $patient      = $idex_patient->loadTargetObject();

    if (!$patient->_id) {
      $patient->nom             = $row["patient nom"];
      $patient->prenom          = $row["patient prenom"];
      $patient->prenoms         = trim(implode(' ', [$row["patient prenom 2"], $row["patient prenom 3"]]));
      $patient->nom_jeune_fille = $row["patient nom jf"];
      $patient->naissance       = $row["patient naissance"];
      $patient->sexe            = $row["patient sexe"];

      if (!$patient->loadMatchingPatient()) {
        $patient->civilite  = $row["patient civilite"];
        $patient->tel       = $row["patient tel"];
        $patient->tel2      = $row["patient mob"];
        $patient->email     = $row["patient email"];
        $patient->matricule = $row["patient numero ss"];
        $patient->adresse   = $row["patient adresse"];
        $patient->cp        = $row["patient cp"];
        $patient->ville     = $row["patient ville"];
        $patient->pays      = $row["patient pays"];

        if ($msg = $patient->store()) {
          $errors[] = array(
            "line" => $line,
            "msg"  => $msg,
          );

          continue;
        }
      }
    }

    $idex_patient->object_id = $patient->_id;
    $idex_patient->store();

    // RDV
    $consult_id = $row["rdv ID"];

    if (!$consult_id) {
      continue;
    }

    /** @var CConsultation $consult */
    $idex_consult = CIdSante400::getMatch("CConsultation", $tag, $consult_id);
    $consult      = $idex_consult->loadTargetObject();

    if (!$consult->_id) {
      $consult->patient_id      = $patient->_id;
      $consult->plageconsult_id = $plage->_id;
      $consult->heure           = $row["rdv debut"];
      $consult->loadMatchingObjectEsc();

      $consult->duree  = $row["rdv creneaux"];
      $consult->motif  = $row["rdv motif"];
      $consult->chrono = $plage->date > $today ? CConsultation::PLANIFIE : CConsultation::TERMINE;

      if ($msg = $consult->store()) {
        $errors[] = array(
          "line" => $line,
          "msg"  => $msg,
        );
      }
    }

    $idex_consult->object_id = $consult->_id;
    $idex_consult->store();
  }

  $pb->adv();
}

CAppUI::js("window.parent.displayMsg(" . CMbArray::toJSON($errors) . ")");
CApp::rip();
