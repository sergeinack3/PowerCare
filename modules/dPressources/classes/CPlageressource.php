<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ressources;

use Ox\Core\CMbDT;
use Ox\Core\CPlageHoraire;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CPlageressource
 */
class CPlageressource extends CPlageHoraire {
  const OUT = "#aaa";  // plage échue
  const FREE = "#aae";  // plage libre
  const FREEB = "#88c";  // plage libre à plus d'1 mois
  const BUSY = "#ecc";  // plage occupée
  const BLOCKED = "#eaa";  // plage occupée à moins de 15 jours
  const PAYED = "#aea";  // plage réglée

  // DB Table key
  public $plageressource_id;

  // DB References
  public $prat_id;

  // DB fields
  public $tarif;
  public $libelle;
  public $paye;

  // Form fields
  public $_hour_deb;
  public $_min_deb;
  public $_hour_fin;
  public $_min_fin;
  public $_state;

  //Filter Fields
  public $_date_min;
  public $_date_max;

  // Object References
  /** @var CMediusers */
  public $_ref_prat;
  public $_ref_patients;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                 = parent::getSpec();
    $spec->table          = "plageressource";
    $spec->key            = "plageressource_id";
    $spec->collision_keys = array();

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props              = parent::getProps();
    $props["prat_id"]   = "ref class|CMediusers seekable back|plages_ressource";
    $props["tarif"]     = "currency notNull min|0";
    $props["libelle"]   = "str confidential seekable";
    $props["paye"]      = "bool";
    $props["_date_min"] = "date";
    $props["_date_max"] = "date moreEquals|_date_min";
    $props["_hour_deb"] = "time";
    $props["_hour_fin"] = "time";

    return $props;
  }

  function loadRefsFwd() {
    $this->_ref_prat = new CMediusers();
    $this->_ref_prat->load($this->prat_id);
  }

  function getPerm($permType) {
    if (!$this->_ref_prat) {
      $this->loadRefsFwd();
    }

    return $this->_ref_prat->getPerm($permType);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_hour_deb = CMbDT::transform($this->debut, null, "%H");
    $this->_hour_fin = CMbDT::transform($this->fin, null, "%H");

    // State rules
    if ($this->paye == 1) {
      $this->_state = self::PAYED;
    }
    elseif ($this->date < CMbDT::date()) {
      $this->_state = self::OUT;
    }
    elseif ($this->prat_id) {
      if (CMbDT::date("+ 15 DAYS") > $this->date) {
        $this->_state = self::BLOCKED;
      }
      else {
        $this->_state = self::BUSY;
      }
    }
    elseif (CMbDT::date("+ 1 MONTH") < $this->date) {
      $this->_state = self::FREEB;
    }
    else {
      $this->_state = self::FREE;
    }
  }

  function becomeNext() {
    // Store old datas
    $prat_id = $this->prat_id;
    $libelle = $this->libelle;
    $tarif   = $this->tarif;

    // Store old form fields
    $_hour_deb = $this->_hour_deb;
    $_min_deb  = $this->_min_deb;
    $_hour_fin = $this->_hour_fin;
    $_min_fin  = $this->_min_fin;

    $this->date    = CMbDT::date("+7 DAYS", $this->date);
    $where["date"] = "= '$this->date'";
    $where[]       = "`debut` = '$this->debut' OR `fin` = '$this->fin'";
    if (!$this->loadObject($where)) {
      $this->plageressource_id = null;
    }

    // Restore old fields
    $this->prat_id   = $prat_id;
    $this->libelle   = $libelle;
    $this->tarif     = $tarif;
    $this->_hour_deb = $_hour_deb;
    $this->_min_deb  = $_min_deb;
    $this->_hour_fin = $_hour_fin;
    $this->_min_fin  = $_min_fin;
    $this->updatePlainFields();
  }
}