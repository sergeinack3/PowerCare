<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * File importation class based on pattern matching on files names
 */
class CFileImport implements IShortNameAutoloadable {
  protected $root_dir;
  protected $regex;
  protected $regex_date = "/^(?P<year>\d{4})(?P<month>\d{2})(?P<day>\d{2})$/";
  protected $start;
  protected $step;
  protected $import;
  protected $count_files;

  protected $user_id;
  protected $dirs_to_handle;
  protected $related_objects = array();
  protected $sorted_files = array();
  protected $sibling_objects = array();

  /**
   * CFileImport constructor.
   *
   * @param string $regex      Regex used to match filename
   * @param string $root_dir   Directory used for the files
   * @param int    $start      Start at
   * @param int    $step       Number of folders to import
   * @param bool   $import     Import files or just check them
   * @param string $regex_date Regex for the dates
   */
  public function __construct($regex, $root_dir = null, $start = 0, $step = 50, $import = false, $regex_date = null) {
    $this->root_dir   = ($root_dir) ?: rtrim(CAppUI::conf('dPfiles import_dir'), '\\/');
    $this->regex      = $regex;
    $this->step       = ($step < 0) ? 50 : $step;
    $this->start      = ($start < 0) ? 0 : $start;
    $this->user_id    = CAppUI::conf('dPfiles import_mediuser_id');
    if ($regex_date) {
      $this->regex_date = $regex_date;
    }
    $this->import = $import;
  }

  /**
   * Parse the files and import them
   *
   * @return void
   */
  public function importFiles() {
    $this->dirs_to_handle = glob("{$this->root_dir}/*");
    $this->count_files    = count($this->dirs_to_handle);

    // Handle a number of folders depending on step
    if ($this->step) {
      $this->dirs_to_handle = array_slice($this->dirs_to_handle, $this->start, $this->step);
    }

    $files = $this->checkGoodFiles();
    $this->analyseGoodFiles($files);
    $this->matchFilesWithObjects();

    if ($this->import) {
      $this->importSortedFiles($this->sorted_files);
    }
  }

  /**
   * Check if files already exists
   *
   * @return array
   */
  protected function checkGoodFiles() {
    $files = array();
    $i     = 0;
    // Good and bad files
    foreach ($this->dirs_to_handle as $_file_dir) {
      if ($this->step && $i >= $this->step) {
        break;
      }

      $dir_files = array($_file_dir);
      $prefix    = "";
      if (is_dir($_file_dir)) {
        $dir_files = glob("{$_file_dir}/*");
        $prefix    = utf8_decode(basename($_file_dir)) . '/';
      }

      foreach ($dir_files as $_file) {
        $_filename = utf8_decode(basename($_file));

        $files[$_filename] = array();

        $this->related_objects[$_filename] = null;

        $_cfile            = new CFile();
        $_cfile->file_name = $_filename;
        $_cfile->author_id = $this->user_id;
        $_cfile->loadMatchingObjectEsc();

        if ($_cfile && $_cfile->_id) {
          $this->related_objects[$_filename] = $_cfile;
        }

        if (preg_match($this->regex, $_filename, $_match)) {
          $files[$_filename] = array(
            "path"  => $prefix . $_filename,
            "match" => $_match,
          );
        }
      }

      $i++;
    }

    return $files;
  }

  /**
   * Analyse files that does not exists yet
   *
   * @param array $files Files to check
   *
   * @return void
   */
  protected function analyseGoodFiles($files) {
    // Good files are analyzed
    foreach ($files as $_filename => $_file) {
      $this->sorted_files[$_filename] = array();

      $this->sorted_files[$_filename] = array(
        "fields" => array(),
        "path" => (isset($_file["path"])) ? $_file["path"] : null,
      );

      // Files that did not match
      if (!isset($_file["match"])) {
        continue;
      }

      foreach ($_file["match"] as $_field => $_value) {
        if (is_string($_field)) {
          $this->sorted_files[$_filename]['fields'][$_field] = $_value;
        }
      }
    }
  }

  /**
   * Match the files with objects, create the objects if needed
   *
   * @return void
   */
  protected function matchFilesWithObjects() {
    // Object loading of analyzed files
    foreach ($this->sorted_files as $_filename => $_file) {
      $this->sibling_objects[$_filename] = null;

      // Files that did not match
      if (!$fields = $_file["fields"]) {
        continue;
      }

      $_patient = $this->matchPatient($fields);

      if ($_patient && $_patient->_id) {
        if (isset($fields['sejour_start']) && isset($fields['sejour_end'])) {
          $_sejour = $this->matchSejour($fields, $_patient);

          if ($_sejour && $_sejour->_id) {
            $this->sibling_objects[$_filename] = $_sejour;
            continue;
          }
        }

        $this->sibling_objects[$_filename] = $_patient;
      }
    }
  }

  /**
   * Search a patient matchin the fields
   * Import the patient if in import mode
   *
   * @param array $file Patient fields extracted from the file name
   *
   * @return CPatient
   */
  protected function matchPatient($file) {
    $_patient = new CPatient();

    if (isset($file['IPP']) && $file['IPP']) {
      $_patient->_IPP = $file['IPP'];

      $_patient->loadFromIPP();

      if ($_patient && $_patient->_id) {
        return $_patient;
      }
    }

    if (isset($file['lastname']) && $file['lastname']) {
      $_patient->nom = str_replace('_', ' ', $file['lastname']);
    }

    if (isset($file['firstname']) && $file['firstname']) {
      $_patient->prenom = str_replace('_', ' ', $file['firstname']);
    }

    if (isset($file['birthdate']) && $file['birthdate']) {
      $_patient->naissance = $this->getDate($file['birthdate']);
    }

    $_patient->loadMatchingPatient();

    if (!$_patient->_id && $this->import) {
      if ($msg = $_patient->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("CPatient-msg-create", UI_MSG_OK);
      }
    }

    return $_patient;
  }

  /**
   * Search a CSejour matching the fields
   * Import the CSejour if in import mode
   *
   * @param array    $file    CSejour fields extracted from the file name
   * @param CPatient $patient Patient to use for the CSejour
   *
   * @return CSejour
   */
  protected function matchSejour($file, $patient) {
    $sejour                = new CSejour();
    $sejour->entree_prevue = $this->getDate($file['sejour_start']) . ' 09:00:00';
    $sejour->sortie_prevue = $this->getDate($file['sejour_end']) . ' 20:00:00';
    $sejour->patient_id    = $patient->_id;


    $sejour->loadMatchingSejour();

    if (!$sejour->_id && $this->import) {
      $sejour->praticien_id = $this->user_id;
      $sejour->group_id     = CGroups::loadCurrent()->_id;

      if ($msg = $sejour->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("CSejour-msg-create", UI_MSG_OK);
      }
    }

    return $sejour;
  }

  /**
   * Return a date using the regex for the dates
   *
   * @param string $date Date to transform
   *
   * @return string
   */
  protected function getDate($date) {
    if (preg_match($this->regex_date, $date, $parts)) {
      return $parts['year'] . '-' . $parts['month'] . '-' . $parts['day'];
    }

    return $date;
  }

  /**
   * Import files
   *
   * @param array $to_import_files Names of the files to import
   *
   * @return void
   */
  protected function importSortedFiles($to_import_files) {
    foreach ($to_import_files as $_filename => $_infos) {
      $sibling_object = $this->sibling_objects[$_filename];
      $related_object = $this->related_objects[$_filename];

      // Objet voisin trouvé
      if ($sibling_object && $sibling_object->_id) {
        // Le fichier est déjà associé à un autre objet
        if ($related_object && $related_object->_id) {
          CAppUI::setMsg('common-error-An object is already linked to this file.', UI_MSG_WARNING);
        }
        // Liaison du fichier à l'objet avoisinant
        else {
          $file_path = "{$this->root_dir}/{$_infos['path']}";
          $file_path = utf8_encode($file_path);

          $cfile            = new CFile();
          $cfile->file_name = $_filename;
          $cfile->author_id = $this->user_id;
          $cfile->file_type = CMbPath::guessMimeType($_filename);
          $cfile->doc_size  = filesize($file_path);

          $cfile->setObject($sibling_object);
          $cfile->fillFields();

          $cfile->setCopyFrom($file_path);

          if ($msg = $cfile->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
          }
          else {
            CAppUI::setMsg('CFile-msg-create', UI_MSG_OK);

            $related_objects[$_filename] = $cfile;
          }
        }
      }
      // Pas d'objet avoisinant
      else {
        CAppUI::setMsg('common-error-Unable to find sibling object.', UI_MSG_ERROR);
      }
    }
  }

  /**
   * @return array
   */
  public function getSiblings() {
    return $this->sibling_objects;
  }

  /**
   * @return array
   */
  public function getRelated() {
    return $this->related_objects;
  }

  /**
   * @return mixed
   */
  public function getCount() {
    return $this->count_files;
  }

  /**
   * @return array
   */
  public function getSortedFiles() {
    return $this->sorted_files;
  }
}
