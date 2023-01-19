<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Archive le contenu d'un dossier patient, le rendant non modifiable sur l'ensemble du contenu
 */
class CVerrouDossierPatient extends CMbObject {
  // DB Table key
  public $verrou_dossier_patient_id;

  // DB fields
  public $patient_id;
  public $user_id;
  public $date;
  public $motif;
  public $coordonnees;
  public $medical;
  public $administratif;
  public $doc_hash;
  public $annule;
  public $annule_user_id;
  public $annule_motif;
  public $annule_date;

  /** @var CPatient */
  public $_ref_patient;
  /** @var CMediusers */
  public $_ref_user;
  /** @var CMediusers */
  public $_ref_user_unarchive;
  /** @var CFile */
  public $_ref_archive_file;

  public $_archive_view;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'verrou_dossier_patient';
    $spec->key   = 'verrou_dossier_patient_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["patient_id"]     = "ref class|CPatient notNull back|verrou_dossier_patient";
    $props["user_id"]        = "ref class|CMediusers notNull back|verrou_user";
    $props["date"]           = "dateTime";
    $props["motif"]          = "text";
    $props["coordonnees"]    = "text";
    $props["medical"]        = "bool default|0";
    $props["administratif"]  = "bool default|0";
    $props["doc_hash"]       = "str";
    $props["annule"]         = "bool default|0";
    $props["annule_user_id"] = "ref class|CMediusers back|verrou_annule_user";
    $props["annule_motif"]   = "text";
    $props["annule_date"]    = "dateTime";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $date = CMbDT::transform($this->date, null, CAppUI::conf("date"));
    $time = CMbDT::transform($this->date, null, CAppUI::conf("time"));
    $type = null;

    if ($this->medical && $this->administratif) {
      $type = CAppUI::tr("CVerrouDossierPatient-Medical and administrative");
    }
    elseif ($this->medical) {
      $type = CAppUI::tr("CVerrouDossierPatient-medical");
    }
    elseif ($this->administratif) {
      $type = CAppUI::tr("CVerrouDossierPatient-administratif");
    }

    $this->_archive_view = CAppUI::tr("CVerrouDossierPatient-Folder archive on %s at %s", $date, $time) . " ($type)";
  }

  /**
   * Charge le patient du dossier
   *
   * @return CPatient
   * @throws \Exception
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
  }

  /**
   * Charge l'utilisateur associé à l'archivage du dossier patient
   *
   * @return CMediusers
   * @throws \Exception
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Charge l'utilisateur qui a annulé l'archivage du dossier patient
   *
   * @return CMediusers
   * @throws \Exception
   */
  function loadRefUserUnarchive() {
    return $this->_ref_user_unarchive = $this->loadFwdRef("annule_user_id", true);
  }

  /**
   * Charge le fichier de l'archive
   *
   * @return CFile
   */
  function loadRefArchiveFile() {
    $archive = new CFile();
    $archive->setObject($this);
    $archive->loadMatchingObject();

    return $this->_ref_archive_file = $archive;
  }

  /**
   * @see parent::store()
   */
  function store() {
    $pdf = null;

    if ($msg = parent::store()) {
      return $msg;
    }

    // Archiver le dossier patient en pdf
    if ($this->_id && !$this->annule) {
      $pdf = CApp::fetch("oxCabinet", "ajax_print_global", array("patient_id" => $this->patient_id, "type" => "tous"));
    }

    if ($pdf) {
      $file = new CFile();
      $file->setObject($this);
      $file->file_name = CAppUI::tr("Archive_dossier_patient-") . CMbDT::format(CMbDT::dateTime(), "%d_%m_%Y_%H_%M_%S") . ".pdf";
      $file->file_type = "application/pdf";
      $file->author_id = CMediusers::get()->_id;
      $file->fillFields();
      $file->setContent($pdf);
      $file->store();

      if ($file) {
        //doc hash
        $this->doc_hash = md5($pdf);
      }

      if ($msg = parent::store()) {
        return $msg;
      }
    }
  }
}
