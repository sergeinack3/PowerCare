<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Mediboard\Hospi\CAffectation;

/**
 * The CRepas class
 */
class CRepas extends CMbObject {
  // DB Table key
  public $repas_id;

  // DB Fields
  public $affectation_id;
  public $menu_id;
  public $plat1;
  public $plat2;
  public $plat3;
  public $plat4;
  public $plat5;
  public $boisson;
  public $pain;
  public $date;
  public $typerepas_id;
  public $modif;

  // Object References
  /** @var CAffectation */
  public $_ref_affectation;
  /** @var CMenu */
  public $_ref_menu;
  /** @var CPlat */
  public $_ref_plat1;
  /** @var CPlat */
  public $_ref_plat2;
  /** @var CPlat */
  public $_ref_plat3;
  /** @var CPlat */
  public $_ref_plat4;
  /** @var CPlat */
  public $_ref_plat5;
  /** @var CPlat */
  public $_ref_boisson;
  /** @var CPlat */
  public $_ref_pain;

  // Form fields
  public $_is_modif;
  public $_no_synchro;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'repas';
    $spec->key   = 'repas_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specsParent = parent::getProps();
    $specs       = array(
      "affectation_id" => "ref notNull class|CAffectation back|repas",
      "menu_id"        => "ref class|CMenu back|repas",
      "plat1"          => "ref class|CPlat back|repas1",
      "plat2"          => "ref class|CPlat back|repas2",
      "plat3"          => "ref class|CPlat back|repas3",
      "plat4"          => "ref class|CPlat back|repas4",
      "plat5"          => "ref class|CPlat back|repas5",
      "boisson"        => "ref class|CPlat back|repas_boisson",
      "pain"           => "ref class|CPlat back|repas_pain",
      "date"           => "date",
      "typerepas_id"   => "ref notNull class|CTypeRepas back|repas",
      "modif"          => "bool"
    );

    return array_merge($specsParent, $specs);
  }

  function check() {
    $msg = parent::check();
    if (!$msg) {
      $where                   = array();
      $where["date"]           = $this->_spec->ds->prepare("= %", $this->date);
      $where["affectation_id"] = $this->_spec->ds->prepare("= %", $this->affectation_id);
      $where["typerepas_id"]   = $this->_spec->ds->prepare("= %", $this->typerepas_id);
      if ($this->repas_id) {
        $where["repas_id"] = $this->_spec->ds->prepare("!= %", $this->repas_id);
      }
      $select = "count(`" . $this->_spec->key . "`) AS `total`";

      $sql = new CRequest();
      $sql->addTable($this->_spec->table);
      $sql->addSelect($select);
      $sql->addWhere($where);

      $nbRepas = $this->_spec->ds->loadResult($sql->makeSelect());

      if ($nbRepas) {
        $msg .= "Un repas a déjà été créé, vous ne pouvez pas en créer un nouveau.";
      }
    }

    return $msg;
  }

  function store() {
    $this->updatePlainFields();
    if (!$this->_no_synchro) {
      $service               = $this->getService();
      $where                 = array();
      $where["date"]         = $this->_spec->ds->prepare("= %", $this->date);
      $where["service_id"]   = $this->_spec->ds->prepare("= %", $service->_id);
      $where["typerepas_id"] = $this->_spec->ds->prepare("= %", $this->typerepas_id);
      $validationrepas       = new CValidationRepas;
      $validationrepas->loadObject($where);
      if ($validationrepas->validationrepas_id) {
        $validationrepas->modif = 1;
        $validationrepas->store();
        $this->modif = 1;
      }
    }

    return parent::store();
  }

  function loadRemplacements() {
    $this->_ref_plat1   = new CPlat;
    $this->_ref_plat2   = new CPlat;
    $this->_ref_plat3   = new CPlat;
    $this->_ref_plat4   = new CPlat;
    $this->_ref_plat5   = new CPlat;
    $this->_ref_boisson = new CPlat;
    $this->_ref_pain    = new CPlat;

    $this->_ref_plat1->load($this->plat1);
    $this->_ref_plat2->load($this->plat2);
    $this->_ref_plat3->load($this->plat3);
    $this->_ref_plat4->load($this->plat4);
    $this->_ref_plat5->load($this->plat5);
    $this->_ref_boisson->load($this->boisson);
    $this->_ref_pain->load($this->pain);

    if ($this->plat1 || $this->plat2 || $this->plat3 || $this->plat4 || $this->plat5 || $this->boisson || $this->pain) {
      $this->_is_modif = true;
    }
  }

  function loadRefMenu() {
    $this->_ref_menu = new CMenu;
    $this->_ref_menu->load($this->menu_id);
  }

  function getService() {
    $this->loadRefAffectation();
    $this->_ref_affectation->loadRefLit();
    $this->_ref_affectation->_ref_lit->loadCompleteView();

    return $this->_ref_affectation->_ref_lit->_ref_chambre->_ref_service;
  }

  function loadRefAffectation() {
    $this->_ref_affectation = new CAffectation;
    $this->_ref_affectation->load($this->affectation_id);
  }

  function loadRefsFwd() {
    $this->loadRefAffectation();
    $this->loadRefMenu();
  }
}