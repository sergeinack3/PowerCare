<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Incident / évenement per-opératoire
 */
class CAnesthPerop extends CMbObject {
  public $anesth_perop_id;

  // DB References
  public $operation_id;

  // DB fields
  public $libelle;
  public $datetime;
  public $incident;
  public $categorie_id;
  public $geste_perop_id;
  public $geste_perop_precision_id;
  public $precision_valeur_id;
  public $commentaire;
  public $user_id;  // User who to apply the event perop

  public $_geste_perop_ids;
  public $_view_completed;
    /** @var string */
  public $_perop_section;

  /** @var COperation */
  public $_ref_operation;
  /** @var CAnesthPeropCategorie */
  public $_ref_categorie;
  /** @var CGestePerop */
  public $_ref_geste_perop;
  /** @var CGestePeropPrecision */
  public $_ref_geste_perop_precision;
  /** @var CPrecisionValeur */
  public $_ref_precision_valeur;
  /** @var CFile */
  public $_ref_file;
  /** @var CMediusers */
  public $_ref_user;

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'anesth_perop';
    $spec->key   = 'anesth_perop_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
  function getProps() {
    $props                             = parent::getProps();
    $props["operation_id"]             = "ref notNull class|COperation back|anesth_perops";
    $props["libelle"]                  = "text notNull helped";
    $props["datetime"]                 = "dateTime notNull";
    $props["incident"]                 = "bool default|0";
    $props["categorie_id"]             = "ref class|CAnesthPeropCategorie back|anesths_perop";
    $props["geste_perop_id"]           = "ref class|CGestePerop back|anesths_perop";
    $props["geste_perop_precision_id"] = "ref class|CGestePeropPrecision back|anesth_perops";
    $props["precision_valeur_id"]      = "ref class|CPrecisionValeur back|anesth_perops";
    $props["commentaire"]              = "text";
    $props["user_id"]                  = "ref class|CMediusers back|anesth_perop_user";

    return $props;
  }

  /**
   * @inheritDoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view      = "$this->libelle à " . CMbDT::format($this->datetime, CAppUI::conf("time")) . " le " . CMbDT::format($this->datetime, CAppUI::conf("date"));
    $this->_shortview = $this->libelle;

    $precision        = $this->loadRefGestePeropPrecision();
    $precision_valeur = $this->loadRefPrecisionValeur();

    $this->_view_completed = $this->libelle;

    if ($precision->_id) {
      $this->_view_completed .= " / " . $precision->_view;
    }

    if ($precision_valeur->_id) {
      $this->_view_completed .= " / " . $precision_valeur->_view;
    }
  }

  /**
   * Charge l'intervention
   *
   * @return COperation
   * @throws Exception
   */
  function loadRefOperation() {
    return $this->_ref_operation = $this->loadFwdRef("operation_id", true);
  }

  /**
   * Charge la categorie
   *
   * @return CAnesthPeropCategorie
   * @throws Exception
   */
  function loadRefCategorie() {
    return $this->_ref_categorie = $this->loadFwdRef("categorie_id", true);
  }

  /**
   * Charge le geste perop
   *
   * @return CGestePerop
   * @throws Exception
   */
  function loadRefGestePerop() {
    return $this->_ref_geste_perop = $this->loadFwdRef("geste_perop_id", true);
  }

  /**
   * Load the precision of the gesture perop
   *
   * @return CGestePeropPrecision
   * @throws Exception
   */
  function loadRefGestePeropPrecision() {
    return $this->_ref_geste_perop_precision = $this->loadFwdRef("geste_perop_precision_id", true);
  }

  /**
   * Load the precision value
   *
   * @return CPrecisionValeur
   * @throws Exception
   */
  function loadRefPrecisionValeur() {
    return $this->_ref_precision_valeur = $this->loadFwdRef("precision_valeur_id", true);
  }

  /**
   * Load the user
   *
   * @return CMediusers
   * @throws Exception
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Charge l'image associé au geste Perop ou à la catégorie
   *
   * @return CFile
   * @throws Exception
   */
  function loadRefFile() {
    $file = array();

    $this->loadRefGestePerop()->loadRefFile();
    $this->loadRefCategorie()->loadRefFile();

    if ($this->_ref_geste_perop->_ref_file->_id && $this->_ref_categorie->_ref_file->_id) {
      $file = $this->_ref_geste_perop->_ref_file;
    }
    elseif ($this->_ref_geste_perop->_ref_file->_id) {
      $file = $this->_ref_geste_perop->_ref_file;
    }
    elseif ($this->_ref_categorie->_ref_file->_id) {
      $file = $this->_ref_categorie->_ref_file;
    }

    return $this->_ref_file = $file;
  }

  /**
   * @inheritDoc
   */
  function getPerm($permType) {
    if (!$this->_ref_operation) {
      $this->loadRefOperation();
    }

    return $this->_ref_operation->getPerm($permType);
  }
}
