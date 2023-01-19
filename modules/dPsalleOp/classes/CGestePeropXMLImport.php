<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use DOMElement;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;

/**
 * Import Gesture's perop in XML
 */
class CGestePeropXMLImport extends CMbXMLObjectImport {
  public static $import_dir;

  protected $name_suffix;
  protected $context;
  protected $counter_objects = array(
    "CAnesthPeropChapitre" => array(
      "created" => 0,
      "founded" => 0
    ),
    "CAnesthPeropCategorie" => array(
      "created" => 0,
      "founded" => 0
    ),
    "CGestePerop" => array(
      "created" => 0,
      "founded" => 0
    ),
    "CFile" => array(
      "created" => 0,
      "founded" => 0
    ),
    "CGestePeropPrecision" => array(
      "created" => 0,
      "founded" => 0
    ),
    "CPrecisionValeur" => array(
      "created" => 0,
      "founded" => 0
    )
  );
  protected $imported = array();
  protected $import_order = array(
    // Structure objects
    "//object[@class='CAnesthPeropChapitre']",
    "//object[@class='CAnesthPeropCategorie']",
    "//object[@class='CGestePerop']",
    "//object[@class='CFile']",
    "//object[@class='CGestePeropPrecision']",
    "//object[@class='CPrecisionValeur']",
  );

  /**
   * CGestePeropXMLImport constructor.
   *
   * @param      $filename
   * @param null $context
   *
   * @throws CMbException
   */
  function __construct($filename, $context = null) {
    if (!$context) {
      $context = CGroups::loadCurrent();
    }

    parent::__construct($filename);

    $this->context = $context;
  }

  /**
   * @inheritDoc
   * @throws Exception
   */
  function importObject(DOMElement $element) {
    if (!$element) {
      return;
    }

    $id = $element->getAttribute("id");

    // Avoid importing the same object multiple time from a single XML file
    if (isset($this->imported[$id])) {
      return;
    }

    $this->name_suffix = " (import du " . CMbDT::dateTime() . ")";

    $_class          = $element->getAttribute("class");
    $imported_object = null;
    $object          = null;

    switch ($_class) {
      case "CAnesthPeropChapitre":
        $imported_object = $this->importChapitre($element, $object);
        break;

      case "CAnesthPeropCategorie":
        $imported_object = $this->importCategorie($element, $object);
        break;

      case "CGestePerop":
        $imported_object = $this->importGeste($element, $object);
        break;

      case "CFile":
        $imported_object = $this->importFile($element, $object);
        break;

      case "CGestePeropPrecision":
        $imported_object = $this->importPrecision($element, $object);
        break;

      case "CPrecisionValeur":
        $imported_object = $this->importPrecisionValeur($element, $object);
        break;

      default:
        break;
    }

    if ($imported_object) {
      $this->map[$id] = $imported_object->_guid;
    }

    $this->imported[$id] = true;
  }

  /**
   * Import a CAnesthPeropChapitre from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CAnesthPeropChapitre
   * @throws Exception
   */
  function importChapitre($element, $object) {
    /** @var CAnesthPeropChapitre $_chapitre */
    $_chapitre = $this->getObjectFromElement($element, $object);
    $group     = CGroups::loadCurrent();

    if (!$_chapitre->_id) {
      $_chapitre->group_id = $group->_id;

      $new_chapitre           = new CAnesthPeropChapitre();
      $new_chapitre->group_id = $_chapitre->group_id;
      $new_chapitre->libelle  = $_chapitre->libelle;
      $new_chapitre->loadMatchingObjectEsc();

      if (!$new_chapitre->_id) {
        if ($msg = $_chapitre->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }

        $this->counter_objects[$_chapitre->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_chapitre->_class]["founded"]++;

        return $new_chapitre;
      }
    }

    return $_chapitre;
  }

  /**
   * Import a CAnesthPeropCategorie from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CAnesthPeropCategorie
   * @throws Exception
   */
  function importCategorie($element, $object) {
    /** @var CAnesthPeropCategorie $_categorie */
    $_categorie = $this->getObjectFromElement($element, $object);
    $group      = CGroups::loadCurrent();

    if (!$_categorie->_id) {
      $_categorie->group_id = $group->_id;

      $new_categorie              = new CAnesthPeropCategorie();
      $new_categorie->group_id    = $_categorie->group_id;
      $new_categorie->libelle     = $_categorie->libelle;

      if ($_categorie->chapitre_id) {
        $new_categorie->chapitre_id = $_categorie->chapitre_id;
      }

      $new_categorie->loadMatchingObjectEsc();

      if (!$new_categorie->_id) {
        if ($msg = $_categorie->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }
        $this->counter_objects[$_categorie->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_categorie->_class]["founded"]++;

        return $new_categorie;
      }
    }

    return $_categorie;
  }

  /**
   * Import a CAnesthPeropCategorie from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CGestePerop|null
   * @throws Exception
   */
  function importGeste($element, $object) {
    /** @var CGestePerop $_geste */
    $_geste = $this->getObjectFromElement($element, $object);

    if (!$_geste->_id) {
      $new_geste = new CGestePerop();

      switch ($this->context->_class) {
        case 'CGroups' :
          $_geste->group_id    = $this->context->_id;
          $new_geste->group_id = $this->context->_id;
          break;
        case 'CFunctions' :
          $_geste->function_id    = $this->context->_id;
          $new_geste->function_id = $this->context->_id;
          break;
        case 'CMediusers' :
          $_geste->user_id    = $this->context->_id;
          $new_geste->user_id = $this->context->_id;
          break;
      }

      $new_geste->libelle  = $_geste->libelle;

      if ($_geste->categorie_id) {
        $new_geste->categorie_id = $_geste->categorie_id;
      }

      $new_geste->loadMatchingObjectEsc();

      if (!$new_geste->_id) {
        if ($msg = $_geste->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }
        $this->counter_objects[$_geste->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_geste->_class]["founded"]++;

        return $new_geste;
      }
    }

    return $_geste;
  }

  /**
   * Import a CFile from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CFile
   * @throws Exception
   */
  function importfile($element, $object) {
    /** @var CFile $_file */
    $_file = $this->getObjectFromElement($element, $object);

    if (!$_file->_id) {
      $new_file = new CFile();
      $new_file->setObject($_file->loadTargetObject());
      $new_file->loadMatchingObjectEsc();

      if (!$new_file->_id) {
        $_file->setCopyFrom(dirname($this->file_path) . "/" . $_file->file_real_filename);
        $_file->file_real_filename = null;
        $_file->fillFields();
        $_file->updateFormFields();

        if ($msg = $_file->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }
        $this->counter_objects[$_file->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_file->_class]["founded"]++;

        return $new_file;
      }
    }

    return $_file;
  }

  /**
   * Import a CGestePeropPrecision from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CGestePeropPrecision
   * @throws Exception
   */
  function importPrecision($element, $object) {
    /** @var CGestePeropPrecision $_precision */
    $_precision = $this->getObjectFromElement($element, $object);
    $group      = CGroups::loadCurrent();

    if (!$_precision->_id) {
      $_precision->group_id = $group->_id;

      $new_precision                 = new CGestePeropPrecision();
      $new_precision->group_id       = $_precision->group_id;
      $new_precision->libelle        = $_precision->libelle;

      if ($_precision->geste_perop_id) {
        $new_precision->geste_perop_id = $_precision->geste_perop_id;
      }

      $new_precision->loadMatchingObjectEsc();

      if (!$new_precision->_id) {
        if ($msg = $_precision->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }
        $this->counter_objects[$_precision->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_precision->_class]["founded"]++;

        return $new_precision;
      }
    }

    return $_precision;
  }

  /**
   * Import a CPrecisionValeur from a XML element
   *
   * @param DOMElement $element XML element
   * @param CMbObject  $object  Object found
   *
   * @return CPrecisionValeur
   * @throws Exception
   */
  function importPrecisionValeur($element, $object) {
    /** @var CPrecisionValeur $_precision_valeur */
    $_precision_valeur = $this->getObjectFromElement($element, $object);
    $group             = CGroups::loadCurrent();

    if (!$_precision_valeur->_id) {
      $_precision_valeur->group_id = $group->_id;

      $new_precision_valeur                           = new CPrecisionValeur();
      $new_precision_valeur->group_id                 = $_precision_valeur->group_id;
      $new_precision_valeur->valeur                   = $_precision_valeur->valeur;

      if ($_precision_valeur->geste_perop_precision_id) {
        $new_precision_valeur->geste_perop_precision_id = $_precision_valeur->geste_perop_precision_id;
      }

      $new_precision_valeur->loadMatchingObjectEsc();

      if (!$new_precision_valeur->_id) {
        if ($msg = $_precision_valeur->store()) {
          $this->writeLog($msg, $element, UI_MSG_WARNING);
          $this->setStop(true);

          return null;
        }
        $this->counter_objects[$_precision_valeur->_class]["created"]++;
      }
      else {
        $this->counter_objects[$_precision_valeur->_class]["founded"]++;

        return $new_precision_valeur;
      }
    }

    return $_precision_valeur;
  }

  /**
   * Returns all messages
   */
  function getMessages() {
    $msg = "";

    foreach ($this->counter_objects as $key_class => $counter) {
      if ($this->counter_objects[$key_class]["created"]) {
        $traduction = CAppUI::tr("$key_class-Imported %s", $this->counter_objects[$key_class]["created"]);
        $msg .= "<div>$traduction</div>";
      }

      if ($this->counter_objects[$key_class]["founded"]) {
        $traduction = CAppUI::tr("$key_class-already exists %s", $this->counter_objects[$key_class]["founded"]);
        $msg .= "<div>$traduction</div>";
      }
    }

    return $msg;
  }
}
