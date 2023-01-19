<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * The CMenu class
 */
class CMenu extends CMbObject {
  // DB Table key
  public $menu_id;

  // DB Fields
  public $nom;
  public $group_id;
  public $typerepas;
  public $plat1;
  public $plat2;
  public $plat3;
  public $plat4;
  public $plat5;
  public $boisson;
  public $pain;
  public $diabete;
  public $sans_sel;
  public $sans_residu;
  public $modif;
  public $debut;
  public $repetition;
  public $nb_repet;

  // Object References
  /** @var CTypeRepas */
  public $_ref_typerepas;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'menu';
    $spec->key   = 'menu_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specsParent = parent::getProps();
    $specs       = array(
      "nom"         => "str notNull",
      "group_id"    => "ref notNull class|CGroups back|menus",
      "typerepas"   => "ref notNull class|CTypeRepas back|menus",
      "plat1"       => "str",
      "plat2"       => "str",
      "plat3"       => "str",
      "plat4"       => "str",
      "plat5"       => "str",
      "boisson"     => "str",
      "pain"        => "str",
      "diabete"     => "bool",
      "sans_sel"    => "bool",
      "sans_residu" => "bool",
      "modif"       => "bool",
      "debut"       => "date notNull",
      "repetition"  => "num notNull pos",
      "nb_repet"    => "num notNull pos"
    );

    return array_merge($specsParent, $specs);
  }

  /**
   * @param string     $date         Date
   * @param CTypeRepas $typerepas_id CTypeRepas id
   *
   * @return CMenu|CMenu[]
   */
  function loadByDate($date, $typerepas_id = null) {
    global $g;
    $where = array();
    if ($typerepas_id) {
      $where["typerepas"] = $this->_spec->ds->prepare("= %", $typerepas_id);
    }
    $where["group_id"] = $this->_spec->ds->prepare("= %", $g);
    $where["debut"]    = $this->_spec->ds->prepare("<= %", $date);
    //$where["fin"]      = $this->_spec->ds->prepare(">= %",$date);
    $order = "nom";

    $listRepas = new CMenu;
    $listRepas = $listRepas->loadList($where, $order);
    foreach ($listRepas as $keyRepas => &$repas) {
      if (!$repas->is_actif($date)) {
        unset($listRepas[$keyRepas]);
      }
    }

    return $listRepas;
  }

  /**
   * @param string $date Date
   *
   * @return bool
   */
  function is_actif($date) {
    $date_debut = CMbDT::date("last sunday", $this->debut);
    $date_debut = CMbDT::date("+1 day", $date_debut);
    $numDayMenu = CMbDT::daysRelative($date_debut, $this->debut);

    $nb_weeks = (($this->nb_repet * $this->repetition) - 1);
    $date_fin = CMbDT::date("+$nb_weeks week", $date_debut);
    $date_fin = CMbDT::date("next monday", $date_fin);
    $date_fin = CMbDT::date("-1 day", $date_fin);

    if ($date < $this->debut || $date > $date_fin) {
      return false;
    }

    $nbDays  = CMbDT::daysRelative($date_debut, $date);
    $nbWeeks = floor($nbDays / 7);
    $numDay  = $nbDays - ($nbWeeks * 7);
    if (!$nbWeeks || !fmod($nbWeeks, $this->repetition)) {
      if ($numDay == $numDayMenu) {
        return true;
      }
    }

    return false;
  }

  /**
   * @throws \Exception
   */
  function loadRefsFwd() {
    $this->_ref_typerepas = new CTypeRepas;
    $this->_ref_typerepas->load($this->typerepas);
  }

  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  function updatePlainFields() {
  }
}