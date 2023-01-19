<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Class CPackExamensLabo
 *
 * Pack d'examens
 */
class CPackExamensLabo extends CMbObject {
  // DB Table key
  public $pack_examens_labo_id;

  // DB references
  public $function_id;
  public $code;
  public $obsolete;

  // DB fields
  public $libelle;

  // Form fields
  public $_locked;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CPackItemExamenLabo[] */
  public $_ref_items_examen_labo;

  /** @var CExamenLabo[] */
  public $_ref_examens_labo;

  /**
   * CPackExamensLabo constructor.
   */
  function __construct() {
    parent::__construct();
    $this->_locked =& $this->_external;
  }

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'pack_examens_labo';
    $spec->key   = 'pack_examens_labo_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["code"]        = "num";
    $props["function_id"] = "ref class|CFunctions back|pack_examens";
    $props["libelle"]     = "str notNull";
    $props["obsolete"]    = "bool";
    $props["_locked"]     = "bool";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_shortview = $this->libelle;
    $this->_view      = $this->libelle;
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->loadRefFunction();
  }

  /**
   * Chargement de la fonction reliée
   *
   * @return void
   */
  function loadRefFunction() {
    $this->_ref_function = new CFunctions;
    $this->_ref_function->load($this->function_id);
  }

  /**
   * Chargement des éléments  d'examen de labo
   *
   * @return void
   */
  function loadRefsItemExamenLabo(){
    $item = new CPackItemExamenLabo;
    $ljoin["examen_labo"] = "pack_item_examen_labo.examen_labo_id = examen_labo.examen_labo_id";
    $where = array("pack_examens_labo_id" => "= '$this->pack_examens_labo_id'");
    // Permet d'afficher dans le pack seulement les analyses non obsolètes
    $where["examen_labo.obsolete"] = " = '0'";
    $this->_ref_items_examen_labo = $item->loadList($where, null, null, null, $ljoin);
  }

  /**
   * Chargement des examens de labo
   *
   * @return void
   */
  function loadRefsExamensLabo() {
    $this->loadRefsItemExamenLabo();
    $this->_ref_examens_labo = array();
    foreach ($this->_ref_items_examen_labo as &$_item) {
      $_item->loadRefExamen();
      $_item->_ref_pack_examens_labo =& $this;
      $this->_ref_examens_labo[$_item->examen_labo_id] = $_item->_ref_examen_labo;
    }
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    parent::loadRefsBack();

    $this->loadRefsExamensLabo();
  }

  /**
   * @inheritdoc
   */
  function getPerm($perm_type) {
    if ($this->function_id) {
      $this->loadRefFunction();
      return $this->_ref_function->getPerm($perm_type);
    }

    return true;
  }
}
