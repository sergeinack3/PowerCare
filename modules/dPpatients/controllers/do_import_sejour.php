<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObjectFieldDescription;
use Ox\Core\CValue;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();
ini_set("auto_detect_line_endings", true);

HandlerManager::disableObjectHandler('CSipObjectHandler');
HandlerManager::disableObjectHandler('CSmpObjectHandler');
;
CAppUI::stepAjax("Désactivation du gestionnaire", UI_MSG_OK);

$start    = CValue::post("start");
$count    = CValue::post("count");
$callback = CValue::post("callback");

CApp::setTimeLimit(600);
CApp::setMemoryLimit("512M");

CMbObject::$useObjectCache = false;
$date                      = CMbDT::date();

$file_import = fopen(CAppUI::conf("root_dir") . "/tmp/rapport_import_sejour_$date.txt", "a");
importFile(CAppUI::conf("cegi imports sejour_csv_path"), $start, $count, $file_import);
fclose($file_import);

$start += $count;

file_put_contents(CAppUI::conf("root_dir") . "/tmp/import_cegi_sejour.txt", "$start;$count");

if ($callback) {
  CAppUI::js("$callback($start,$count)");
}

echo "<tr><td colspan=\"2\">MEMORY: " . memory_get_peak_usage(true) / (1024 * 1024) . " MB" . "</td>";
CMbObject::$useObjectCache = true;
CApp::rip();

/**
 * import the patient file
 *
 * @param string   $file        path to the file
 * @param int      $start       start int
 * @param int      $count       number of iterations
 * @param resource $file_import file
 *
 * @return null
 */
function importFile($file, $start, $count, $file_import) {
  $group = CGroups::loadCurrent();
  $fp    = fopen($file, 'r');

  // refs
  $patient  = new CPatient();
  $mediuser = new CMediusers();
  // sejour
  $sejour       = new CSejour();
  $sejour_specs = CModelObjectFieldDescription::getSpecList($sejour);
  CModelObjectFieldDescription::removeSpecs(array("codes_ccam", "tarif", "pathologie", ""), $sejour_specs);
  //ajouts
  CModelObjectFieldDescription::addBefore($mediuser->_specs["adeli"], $sejour_specs, "praticien_id");
  CModelObjectFieldDescription::addAfter($mediuser->_specs["rpps"], $sejour_specs, "praticien_id");
  CModelObjectFieldDescription::addAfter($mediuser->_specs["_user_first_name"], $sejour_specs, "praticien_id");
  CModelObjectFieldDescription::addAfter($mediuser->_specs["_user_last_name"], $sejour_specs, "praticien_id");
  CModelObjectFieldDescription::addBefore($patient->_specs["_IPP"], $sejour_specs, "patient_id", true);
  CModelObjectFieldDescription::addBefore($sejour->_specs["_NDA"], $sejour_specs, "main", false, true);

  $_sejour_specs = CModelObjectFieldDescription::getArray($sejour_specs);
  echo count($_sejour_specs) . " traits d'import";


  //0 = first line
  if ($start == 0) {
    $start++;
  }

  $line_nb = 0;
  while ($line = fgetcsv($fp, null, ";")) {

    if ($line_nb >= $start && $line_nb < ($start + $count)) {
      $line_rapport = "ligne $line_nb - ";
      $sejour       = new CSejour();
      $patient      = new CPatient();
      $mediuser     = new CMediusers();
      $nda          = null;
      $adeli        = null;
      $rpps         = null;
      $medi_name    = null;
      $medi_first   = null;

      //foreach SPECS, first load
      foreach ($_sejour_specs as $key => $_specs) {
        $field = $_specs->fieldName;

        // NDA
        if ($field == "_NDA") {
          $nda = $line[$key];
        }
        // patient
        if ($field == "_IPP") {
          $patient->_IPP = $line[$key];
          continue;
        }
        // mediuser
        if ($field == "adeli") {
          $adeli = $line[$key];
          continue;
        }
        if ($field == "rpps") {
          $rpps = $line[$key];
          continue;
        }

        if ($field == "_user_first_name") {
          $medi_first = $line[$key];
          continue;
        }

        if ($field == "_user_last_name") {
          $medi_name = $line[$key];
          continue;
        }

        $sejour->$field = $line[$key];
      }

      $sejour_full = $sejour;

      // works
      $sejour->group_id = $group->_id;

      //loading patient
      $patient->loadFromIPP($group->_id);
      if (!$patient->_id) {
        $line_rapport .= " [Patient non trouvé via IPP]";
      }
      $sejour->patient_id = $patient->_id;

      // loading mediuser
      if (!$mediuser->_id && $adeli) {
        $mediuser->adeli = $adeli;
        $mediuser->loadMatchingObjectEsc();
      }
      else {
        $line_rapport .= " [pas d'ADELI]";
      }

      if (!$mediuser->_id && $rpps) {
        $mediuser->adeli = null;
        $mediuser->rpps  = $rpps;
        $mediuser->loadMatchingObjectEsc();
      }

      // classic search and creation
      if (!$mediuser->_id && $medi_first && $medi_name) {
        $mediuser->adeli            = null;
        $mediuser->rpps             = null;
        $mediuser->_user_first_name = $medi_first;
        $mediuser->_user_last_name  = $medi_name;
        createPraticien($mediuser, $group);
        if ($mediuser->_id) {
          if ($adeli) {
            $mediuser->adeli = $adeli;
          }
          if ($rpps) {
            $mediuser->rpps = $rpps;
          }
          $mediuser->store();
        }
      }

      if (!$medi_first && !$medi_name && !$rpps && !$adeli) {
        $line_rapport .= " [ /!\\ Aucune donnée pour le médecin]";
      }

      // nothing at all
      if (!$mediuser->_id) {
        $line_rapport         .= " [prat non trouvé, utilisation du prat par défaut]";
        $user                 = new CUser();
        $user->user_last_name = CAppUI::conf("hprimxml medecinIndetermine") . " $group->_id";
        $user->loadMatchingObjectEsc();
        if (!$user->_id) {
          $mediuser                  = new CMediusers();
          $mediuser->_user_last_name = $user->user_last_name;
          createPraticien($mediuser, $group, true);
        }
        $user->loadRefMediuser();
        $mediuser = $user->_ref_mediuser;
      }

      $sejour->praticien_id = $mediuser->_id;

      //try nda
      if (!$sejour->_id && $nda) {
        $sejour->loadFromNDA($nda);
      }

      if (!$sejour->_id) {
        $sejour->loadMatchingSejour();
      }

      foreach ($sejour as $key => $value) {
        if ($sejour_full->$key && !$sejour->$key && $value) {
          $sejour->$key = $value;
        }
      }


      //found
      if ($sejour->_id) {
        $line_rapport .= " - séjour déjà existant";
        $sejour->updatePlainFields();
        //check NDA
        if (!$sejour->_NDA && $nda) {
          $idex = CIdSante400::getMatch($sejour->_class, CSejour::getTagNDA(), $nda, $sejour->_id);
          $msg  = $idex->store();
          if ($msg) {
            $line_rapport .= " - NDA non créé : $msg";
          }
          else {
            $line_rapport .= " - NDA créé sur le séjour";
          }
        }
        echo "<tr style=\"color:orange\"><td>$line_nb</td><td>Séjour [$sejour->_view] déjà existant (nda : $sejour->_NDA)</td></tr>";
      }
      else { //not found

        //collision
        $sejours_collide = $sejour->getCollisions();

        if (!count($sejours_collide)) {
          $result = $sejour->store();
          if (!$result) {
            //create NDA
            $idex = CIdSante400::getMatch($sejour->_class, CSejour::getTagNDA(), $nda, $sejour->_id);
            $idex->store();
            $line_rapport .= " - séjour créé avec NDA";
            echo "<tr style=\"color:green\"><td>$line_nb</td><td>séjour $sejour créé (nda : $nda)</td></tr>";
          }
          else {
            $line_rapport .= " - séjour non créé : $result";
            CApp::log("Log from do_import_sejour", "LINE $line_nb, ERREUR : $result");
              echo "<tr  style=\"color:red\"><td>$line_nb</td><td><div class=\"error\">le séjour $sejour n'a pas été créé<br/>
            $result</div></td></tr>";
          }
        }
        else {
          $line_rapport .= " - séjour non créé, " . count($sejours_collide) . "collisions";
          echo "<tr style=\"color:red\"><td>$line_nb</td><td>Ce séjour a plusieurs collisions (" . count($sejours_collide) . ") (non traité)</td></tr>";
        }
      }
      $line_rapport .= "\n";
      fwrite($file_import, $line_rapport);
    }

    if ($line_nb > ($start + $count)) {
      break;
    }
    $line_nb++;
  }
}

function createPraticien(CMediusers &$mediuser, $group, $indetermine = false) {
  $functions           = new CFunctions();
  $functions->text     = CAppUI::conf("hprimxml functionPratImport");
  $functions->group_id = $group->_id;

  if (!$functions->loadMatchingObjectEsc()) {
    $functions->type            = "cabinet";
    $functions->color           = "FFFFFF";
    $functions->compta_partagee = 0;
    if ($msg = $functions->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
    }
  }
  $mediuser->function_id = $functions->_id;
  $mediuser->makeUsernamePassword($mediuser->_user_first_name, $mediuser->_user_last_name, null, true);
  $mediuser->_user_type  = 13; // Medecin
  $mediuser->actif       = 0;
  $user                  = new CUser();
  $user->user_last_name  = $mediuser->_user_last_name;
  $user->user_first_name = $mediuser->_user_first_name;
  if ($indetermine) {
    $user->loadMatchingObjectEsc();
  }
  else {
    //seek
    $listPrat = $user->seek("$user->user_last_name $user->user_first_name");
    if (count($listPrat) == 1) {
      $user = reset($listPrat);
      $user->loadRefMediuser();
      $mediuser = $user->_ref_mediuser;
    }
    else {
      if ($msg = $mediuser->store()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
      }
    }
  }


  return $mediuser;
}
