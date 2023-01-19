<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Mediboard\Bioserveur\CBioServeurAccount;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CDocumentExterne extends CMessageExterne {

  public $_account_class;

  public $patient_lastname;
  public $patient_firstname;
  public $patient_birthdate;

  public $document_date;
  public $document_name;

  public $_status;

  static $_status_available = array(
    "unlinked",
    "linked",
    "archived",
    "starred",
    "all"
  );

  /** @var CPatient */
  public $_ref_patient;

  /** @var  CBioServeurAccount|CMediusers */
  public $_ref_account;

  public $_ref_file;

  public $_nb_unlinked_item;
  public $_nb_linked_item;
  public $_nb_archived_item;
  public $_nb_starred_item;
  public $_nb_all_item;
  public $_nb_total_item;


  /**
   * get the list of accounts
   */
  function getAccountList() {
    return array();
  }

  /**
   * @see parent::getProps
   */
  function getProps() {
    $props = parent::getProps();

    $props["patient_lastname"]  = "str";
    $props["patient_firstname"] = "str";
    $props["patient_birthdate"] = "birthDate";

    $props["document_date"]       = "dateTime";
    $props["document_name"]       = "str";

    return $props;
  }

  /**
   * @return CBioServeurAccount|CMediusers
   */
  function loadRefAccount() {
    return $this->_ref_account = $this->loadFwdRef("account_id", true);
  }


  /**
   * return a list of documents
   *
   * @param string $mode  mode of documents
   * @param int    $start begin of answer
   * @param int    $limit number by pagination
   *
   * @return null
   */
  function get_document_list($mode, $start = 0, $limit = 50) {
    return null;
  }

  /**
   * count the number of document for this account
   *
   * @param null $mode
   *
   * @return mixed
   */
  function count_document_total($mode = null) {
    if ($mode) {
      $name = "_nb_".$mode."_item";
      return $this->$name;
    }
    return $this->_nb_total_item;
  }


  /**
   * Find the patient following data
   *
   * @return CPatient $patient
   */
  function findPatient() {
    $patient = new CPatient();
    return $this->_ref_patient = $patient;
  }


  /**
   * return a patient following db data
   *
   * @return CPatient $patient
   */
  function loadPatientByData() {
    $patient = new CPatient();
    $patient->nom = $this->patient_lastname;
    $patient->prenom = $this->patient_firstname;
    $patient->naissance = $this->patient_birthdate;
    $patient->loadMatchingPatient();
    return $patient;
  }

  /**
   * load the file attached
   *
   * @param boolean $fwd if file is linked, try to recover the file by fwd method
   *
   * @return CFile
   */
  function loadRefFile($fwd = true) {
    return $this->_ref_file;
  }

}
