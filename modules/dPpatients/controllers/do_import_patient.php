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
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CModelObjectFieldDescription;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CPhoneSpec;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();
ini_set("auto_detect_line_endings", true);

HandlerManager::disableObjectHandler('CSipObjectHandler');
HandlerManager::disableObjectHandler('CSmpObjectHandler');

CAppUI::stepAjax("Désactivation du gestionnaire", UI_MSG_OK);

$start    = CValue::post("start");
$count    = CValue::post("count");
$callback = CValue::post("callback");
$date     = CMbDT::date();

CApp::setTimeLimit(600);
CApp::setMemoryLimit("512M");

CMbObject::$useObjectCache = false;

$file_import = fopen(CAppUI::conf("root_dir") . "/tmp/rapport_import_patient_$date.txt", "a");
importFile(CAppUI::conf("dPpatients imports pat_csv_path"), $start, $count, $file_import);
fclose($file_import);

$start += $count;
file_put_contents(CAppUI::conf("root_dir") . "/tmp/import_patient.txt", "$start;$count");

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
 * @param resource $file_import file for report
 *
 * @return null
 */
function importFile($file, $start, $count, $file_import) {
  $fp = fopen($file, 'r');


  $patient       = new CPatient();
  $patient_specs = CModelObjectFieldDescription::getSpecList($patient);
  CModelObjectFieldDescription::addBefore($patient->_specs["_IPP"], $patient_specs);
  /** @var CMbFieldSpec[] $_patient_specs */
  $_patient_specs = CModelObjectFieldDescription::getArray($patient_specs);
  echo count($_patient_specs) . " traits d'import";

  //0 = first line
  if ($start == 0) {
    $start++;
  }

  $line_nb = 0;
  while ($line = fgetcsv($fp, null, ";")) {
    $patient = new CPatient();
    if ($line_nb >= $start && $line_nb < ($start + $count)) {
      $line_rapport = "ligne $line_nb - ";

      //foreach SPECS, first load
      foreach ($_patient_specs as $key => $_specs) {
        $field = $_specs->fieldName;
        $data  = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $line[$key]);

        //specific cleanups
        if ($_specs instanceof CPhoneSpec) {
          $data = preg_replace('/\D/', '', $data);
        }

        if ($field == "sexe") {
          $data = strtolower($data);
        }

        if ($field == "deces" && $data == "0000-00-00") {
          $data = null;
        }

        $patient->$field = $data;
      }

      $line_rapport .= "Patient $patient->nom $patient->prenom ($patient->naissance)";

      //clone and IPP
      $IPP                    = $patient->_IPP;
      $patient->_generate_IPP = false;
      $patient_full           = new CPatient();
      $patient_full->extendsWith($patient);

      // load by ipp if basic didn't find.
      if (!$patient->_id) {
        $patient->loadFromIPP();
        if ($patient->_id) {
          $line_rapport .= " (trouvé par IPP)";
        }
      }

      //load patient with basics
      if (!$patient->_id) {
        $patient->_IPP = null;
        $patient->loadMatchingPatient();
        if ($patient->_id) {
          $line_rapport .= " (trouvé par matching)";
        }
      }

      //update fields if import have more data
      foreach ($patient->getPlainFields() as $field => $value) {
        if (!$patient->$field) {
          $patient->$field = $patient_full->$field;
        }
      }

      // fields created by store, let the store do the job for these
      $patient->civilite = "guess";

      //found
      if ($patient->_id) {
        //check IPP
        $patient->loadIPP();

        //update
        $patient->store();

        if (!$patient->_IPP) {
          $idex = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP(), $IPP, $patient->_id);
          $idex->store();
          if ($idex->_id) {
            $line_rapport .= ", IPP créé : $IPP";
          }
          echo "<tr style=\"color:#c98000\"><td>$line_nb</td><td>patient [$patient->nom $patient->prenom] déjà existant (MAJ ipp : $idex->id400)</td></tr>";
        }
        else {
          $line_rapport .= " déjà existant";
          if ($patient->_IPP != $IPP) {
            CApp::log("Log from do_import_patient", $patient->_view . " [ipp: " . $patient->_IPP . " / ipp_import:" . $IPP);
            $line_rapport .= " [IPP du fichier: $IPP / ipp en base: $patient->_IPP ]";
          }
          $line_rapport .= " [IPP en base et fichier identiques]";
          echo "<tr style=\"color:#c98000\"><td>$line_nb</td><td>patient [$patient->nom $patient->prenom] déjà existant (ipp : $patient->_IPP)</td></tr>";
        }
      }
      //not found
      else {
        $result = $patient->store();
        if (!$result) {
          $line_rapport .= " créé avec succes";
          //create IPP
          $idex = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP(), $IPP, $patient->_id);
          if ($idex->_id) {
            $line_rapport .= ", IPP précédente : $idex->id400";
          }
          $idex->store();
          if ($idex->_id) {
            $line_rapport .= ", IPP enregistrée : $idex->id400";
          }
          echo "<tr style=\"color:green\"><td>$line_nb</td><td>patient [$patient->nom $patient->prenom] créé (ipp : $idex->id400)</td></tr>";
        }
        // error while storing
        else {
          $patient->repair();
          $result       = $patient->store();
          $line_rapport .= " réparé et créé";
          if (!$result) {
            //create IPP
            $idex = CIdSante400::getMatch($patient->_class, CPatient::getTagIPP(), $IPP, $patient->_id);
            if ($idex->_id) {
              $line_rapport .= ", IPP précédente : $idex->id400";
            }
            $idex->store();
            if ($idex->_id) {
              $line_rapport .= ", IPP enregistrée : $idex->id400";
            }
            echo "<tr style=\"color:green\"><td>$line_nb</td><td>patient [$patient->nom $patient->prenom] créé (ipp : $idex->id400)</td></tr>";
          }
          else {
            $line_rapport .= " non créé : $result";
            CApp::log("Log from do_import_patient", "LINE $line_nb : erreur: " . $result);
              echo "<tr  style=\"color:red\"><td>$line_nb</td><td>
              <div class=\"error\">le patient [$patient->nom $patient->prenom] n'a pas été créé<br/>
            erreur: $result</div></td></tr>";
          }
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
