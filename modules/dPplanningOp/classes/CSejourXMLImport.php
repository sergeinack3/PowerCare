<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use DOMElement;
use DOMNodeList;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CDateTimeSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Class CSejourXMLImport
 */
class CSejourXMLImport extends CMbXMLObjectImport {
  protected $name_suffix;

  protected $imported = array();

  protected $import_order = array(
    // Structure objects
    "//object[@class='CGroups']",
    "//object[@class='CMediusers']",
    "//object[@class='CUser']",
    "//object[@class='CService']",
    "//object[@class='CFunctions']",
    "//object[@class='CChambre']",
    "//object[@class='CLit']",

    "//object[@class='CPatient']",
    "//object[@class='CSejour']",
    "//object[@class='CGrossesse']",
    "//object[@class='COperation']",
    "//object[@class='CConsultation']",
    "//object[@class='CNaissance']",
    "//object[@class='CDossierPerinat']",
    "//object",
  );

  protected $directory;

  protected $files_directory;

  protected $update_data = false;

  static $_ignored_classes = array("CPrescription", "CBlocOperatoire", "CMediusers");

  /** @var CSejour $sejour */
  protected $sejour = null;

  /** @var CService $service */
  protected $service = null;

  /** @var CChambre $service */
  protected $chambre = null;

  /**
   * @inheritdoc
   */
  function importObject(DOMElement $element) {
    $id = $element->getAttribute("id");

    if (isset($this->imported[$id])) {
      return;
    }
    $this->name_suffix = " (import du " . CMbDT::dateTime() . ")";

    $_class          = $element->getAttribute("class");
    $imported_object = null;

    $object = null;

    switch ($_class) {
      case "CPatient":
        $imported_object = $this->importPatient($element, $object);
        break;

      case "CSejour":
        $_object = $this->importSejour($element, $object);

        $this->sejour    = $_object;
        $imported_object = $_object;
        break;

      case "COperation":
        $_interv = $this->importOperation($element, $object);
        if (!$_interv) {
          break;
        }
        $imported_object = $_interv;

        //Chargement du protocole
        /** @var DOMNodeList $prescriptionElmnt */
        $prescriptionElmnt = $this->xpath->query("//*[@class='CPrescription']");
        if ($prescriptionElmnt->length > 0) {
          $this->importPrescription($_interv, $prescriptionElmnt, $object);
        }
        break;

      case "CGroups":
        /** @var CGroups $_new_group */
        $_new_group = CGroups::loadCurrent();

        if ($_new_group->_id) {
          $_object         = $_new_group;
          $imported_object = $_object;

          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CService":
        $_object = $this->importService($element, $object);

        if ($_object->_id) {
          $this->service   = $_object;
          $imported_object = $_object;
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CChambre":
        $_object = $this->importChambre($element, $object);

        if ($_object->_id) {
          $this->chambre   = $_object;
          $imported_object = $_object;
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CLit":
        $_object = $this->importLit($element, $object);

        if ($_object->_id) {
          $imported_object = $_object;
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CSalle":
        /** @var CSalle $_object */
        $_object = $this->getObjectFromElement($element, $object);
        $_object->loadObject();

        if ($_object->_id) {
          $imported_object = $_object;
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CUser":
        /** @var CUser $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $_new_user                = new CUser();
        $_new_user->user_username = $_object->user_username;
        $_new_user->loadMatchingObject();
        $_object         = $_new_user->loadRefMediuser();
        $imported_object = $_object;
        if ($imported_object) {
          $guid             = $this->getMediuserGuid();
          $this->map[$guid] = $imported_object->_guid;
        }
        $this->imported["CMediusers-" . $_object->_id] = true;
        if ($_new_user->_id) {
          $_object         = $_new_user;
          $imported_object = $_object;
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        break;

      case "CGrossesse":
        $_object = $this->importGrossesse($element, $object);

        if ($_object->_id) {
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        }
        else {
          if ($msg = $_object->store()) {
            CAppUI::stepAjax($msg, UI_MSG_WARNING);
            break;
          }
          CAppUI::stepAjax("Grossesse '%s' créé", UI_MSG_OK, $_object->_view);
        }
        $imported_object = $_object;
        break;

      case "CFunctions":
        /** @var CFunctions $_object */
        $_object = $this->getObjectFromElement($element, $object);
        $_object->loadMatchingObject();
        if ($_object->_id) {
          CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
          $imported_object = $_object;
        }
        break;
      default:
        // Ignored classes
        if (in_array($_class, self::$_ignored_classes)) {
          break;
        }

        $_object = $this->getObjectFromElement($element, $object);
        $this->mapExtraFields($_object);

        if (!$this->storeObject($_object)) {
          break;
        }

        $imported_object = $_object;
        break;
    }

    if ($imported_object) {
      $this->map[$id] = $imported_object->_guid;
    }

    $this->imported[$id] = true;
  }

  /**
   * Store an object and set it's status
   *
   * @param CStoredObject $object  Object to store
   * @param DOMElement    $element Element to use for logging
   *
   * @return bool
   */
  function storeObject(CStoredObject $object, $element = null, bool $repair = false) {
    $is_new   = !$object->_id;
    $modified = false;

    if (!$is_new) {
      $modified = $object->objectModified();
    }

    if ($msg = $object->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);

      return false;
    }

    if ($is_new) {
      CAppUI::stepAjax(CAppUI::tr($object->_class . "-msg-create") . " : " . $object->_view, UI_MSG_OK);
    }
    elseif ($modified) {
      CAppUI::stepAjax(CAppUI::tr($object->_class . "-msg-modify") . " : " . $object->_view, UI_MSG_OK);
    }

    return true;
  }

  /**
   * Set specific fields values according to xml
   *
   * @param CStoredObject $object Object to import
   *
   * @return void
   */
  function mapExtraFields($object) {
    $this->manageObjectDate($object);

    switch ($object->_class) {
      case "CSejour":
        /** @var CSejour $object */
        if ($object->sortie_prevue[0] == "+") {
          $object->sortie_prevue = CMbDT::dateTime("+$object->sortie_prevue DAYS", $object->entree);
        }

        if ($object->praticien_id == "selenium") {
          $user                  = new CUser();
          $user->user_last_name  = "CHIR";
          $user->user_first_name = "Test";

          $user->loadMatchingObject();

          $object->praticien_id = $user->_id;
        }

        if ($object->patient_id == "selenium") {
          $patient         = new CPatient();
          $patient->nom    = "PATIENTLASTNAME";
          $patient->prenom = "Patientfirstname";
          $patient->loadMatchingObject();

          $object->patient_id = $patient->_id;
        }
        break;
      default:
        // Skip
        break;
    }

  }

  /**
   * Get the first mediuser guid
   *
   * @return string|null
   */
  function getMediuserGuid() {
    $_element = $this->xpath->query("//*[@class='CMediusers']")->item(0);
    foreach ($_element->attributes as $_attribute) {
      $_name = $_attribute->name;
      if ($_name == "id") {
        return $_attribute->value;
      }
    }

    return null;
  }

  /**
   * Set directory path
   *
   * @param string $directory Directory path
   *
   * @return void
   */
  function setDirectory($directory) {
    $this->directory = $directory;
  }

  /**
   * Manage object date and dateTime fields if 'now' or 'now 12:00:00' is provided in xml
   *
   * @param CStoredObject $object Object
   *
   * @return void
   */
  function manageObjectDate($object) {
    $plainFields = $object->getPlainFields();

    foreach ($plainFields as $_field => $_value) {
      if (strpos($_value, 'now') !== false) {
        $specs = $object->getSpecs();
        if ($specs[$_field] instanceof CDateSpec) {
          $object->$_field = CMbDT::date();
        }

        $splitedValue = explode(' ', $_value);
        if ($specs[$_field] instanceof CDateTimeSpec) {
          if (isset($splitedValue[1])) {
            $object->$_field = CMbDT::date() . " " . $splitedValue[1];
          }
          else {
            $object->$_field = CMbDT::dateTime();
          }
        }
      }
    }
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CMbObject|CPatient|null
   */
  function importPatient($element, $object) {
    /** @var CPatient $_patient */
    $_patient = $this->getObjectFromElement($element, $object);

    $this->mapExtraFields($_patient);
    if ($_patient->naissance == "0000-00-00") {
      $_patient->naissance = "1850-01-01";
    }

    $_patient->loadMatchingPatient();
    $_patient->civilite = 'guess';

    $is_new = !$_patient->_id;
    if ($is_new) {
      if ($msg = $_patient->store()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);

        return null;
      }
      CAppUI::stepAjax("Patient '%s' créé", UI_MSG_OK, $_patient->_view);
    }
    else {
      CAppUI::stepAjax("Patient '%s' retrouvé", UI_MSG_OK, $_patient->_view);
    }

    return $_patient;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CMbObject|CSejour|mixed|null
   */
  function importSejour($element, $object) {
    /** @var CSejour $_object */
    $_object = $this->getObjectFromElement($element, $object);

    $this->mapExtraFields($_object);

    $_collisions = $_object->getCollisions();

    if (count($_collisions)) {
      $_object = reset($_collisions);
      CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvé", UI_MSG_OK, $_object);
    }
    else {
      if (!$this->storeObject($_object)) {
        return null;
      }
    }

    return $_object;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CMbObject|CService
   */
  function importService($element, $object) {
    /** @var CService $_object */
    $_object           = $this->getObjectFromElement($element, $object);
    $where             = array();
    $where['nom']      = "LIKE '%$_object->nom%'";
    $group             = CGroups::loadCurrent();
    $where['group_id'] = "= $group->_id";
    $_object->loadObject($where);

    return $_object;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CChambre|CMbObject
   */
  function importChambre($element, $object) {
    /** @var CChambre $_object */
    $_object             = $this->getObjectFromElement($element, $object);
    $where               = array();
    $where['nom']        = "LIKE '%$_object->nom%'";
    $where['annule']     = "= '0'";
    $where['service_id'] = "= " . $this->service->_id;
    $_object->loadObject($where);

    return $_object;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CLit|CMbObject
   */
  function importLit($element, $object) {
    /** @var CLit $_object */
    $_object             = $this->getObjectFromElement($element, $object);
    $where               = array();
    $where['nom']        = "LIKE '%$_object->nom%'";
    $where['chambre_id'] = "= " . $this->chambre->_id;
    $where['annule']     = "= '0'";
    $_object->loadObject($where);

    return $_object;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return \___PHPSTORM_HELPERS\static|CMbObject|COperation|mixed|null
   */
  function importOperation($element, $object) {
    /** @var COperation $_interv */
    $_interv = $this->getObjectFromElement($element, $object);
    $_ds     = $_interv->getDS();

    $this->mapExtraFields($_interv);

    $where     = array(
      "sejour_id" => $_ds->prepare("= ?", $_interv->sejour_id),
      "chir_id"   => $_ds->prepare("= ?", $_interv->chir_id),
      "date"      => $_ds->prepare("= ?", $_interv->date),
    );
    $_matching = $_interv->loadList($where);

    if (count($_matching)) {
      $_interv = reset($_matching);
      CAppUI::stepAjax("%s '%s' retrouvée", UI_MSG_OK, CAppUI::tr($_interv->_class), $_interv->_view);
    }
    else {
      $is_new = !$_interv->_id;
      if ($msg = $_interv->store(false)) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
        return null;
      }
      CAppUI::stepAjax("%s '%s' " . ($is_new ? "créée" : "mise à jour"), UI_MSG_OK, CAppUI::tr($_interv->_class), $_interv->_view);
    }

    return $_interv;
  }

  /**
   * @param DOMElement $element Node to import
   * @param CMbObject  $object  Object found
   *
   * @return CGrossesse|CMbObject
   */
  function importGrossesse($element, $object) {
    /** @var CGrossesse $_object */
    $_object = $this->getObjectFromElement($element, $object);

    $this->mapExtraFields($_object);

    $where                   = array();
    $where["terme_prevu"]    = "= '$_object->terme_prevu'";
    $where["parturiente_id"] = "= '$_object->parturiente_id'";
    $_object->loadObject($where);

    return $_object;
  }

  /**
   * @param COperation  $_interv           Operation created
   * @param DOMNodeList $prescriptionElmnt Node to import
   * @param CMbObject   $object            Object found
   *
   * @return void
   */
  function importPrescription($_interv, $prescriptionElmnt, $object) {
    /** @var DOMElement $_element */
    $_element = $prescriptionElmnt->item(0);
    /** @var CPrescription $_protocole */
    $_protocole = $this->getObjectFromElement($_element, $object);
    $_protocole->loadMatchingObject();

    $_protocole->loadRefsLinesElement();
    $_protocole->loadRefsLinesMed();
    $_protocole->loadRefsPrescriptionLineMixes();

    $prescription               = new CPrescription();
    $prescription->object_class = 'CSejour';
    $prescription->object_id    = $this->sejour->sejour_id;
    $prescription->type         = 'sejour';

    $prescription->loadMatchingObject();

    if (!$prescription->_id) {
      if ($msg = $prescription->store()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
        return;
      }
      CAppUI::stepAjax("Prescription '%s' créé", UI_MSG_OK, $prescription->_view);
    }
    else {
      CAppUI::stepAjax("Prescription '%s' retrouvée", UI_MSG_OK, $prescription->_view);
    }

    $_interv->loadRefPraticien();
    $prescription->_active = true;
    $prescription->applyPackOrProtocole(
      'prot-' . $_protocole->_id, $_interv->_ref_praticien->_id, CMbDT::date(), null, $_interv->_id, null
    );

    // Signature des lignes de prescriptions
    $prescription->loadRefsLinesMed();
    foreach ($prescription->_ref_prescription_lines as $_prescription_line) {
      $_prescription_line->signee = 1;
      $_prescription_line->store();
    }
    $prescription->loadRefsPrescriptionLineMixes();
    foreach ($prescription->_ref_prescription_line_mixes as $_prescription_line_mix) {
      $_prescription_line_mix->signature_prat = 1;
      $_prescription_line_mix->store();
    }
    $prescription->loadRefsLinesElement();
    foreach ($prescription->_ref_prescription_lines_element as $_prescription_line_elem) {
      $_prescription_line_elem->signee = 1;
      $_prescription_line_elem->store();
    }
  }
}
