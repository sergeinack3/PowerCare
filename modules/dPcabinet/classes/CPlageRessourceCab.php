<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CPlageHoraire;

/**
 * Description
 */
class CPlageRessourceCab extends CPlageHoraire {
  /** @var integer Primary key */
  public $plage_ressource_cab_id;

  // DB Fields
  public $ressource_cab_id;
  public $libelle;
  public $freq;
  public $color;

  // References
  /** @var CReservation[] */
  public $_ref_reservations = [];
  /** @var CRessourceCab */
  public $_ref_ressource;

  // Form fields
  public $_freq;
  public $_cumulative_minutes;
  public $_repeat;
  public $_type_repeat;
  public $_count_duplicated_plages;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "plage_ressource_cab";
    $spec->key = "plage_ressource_cab_id";
    $spec->collision_keys = array("ressource_cab_id");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["ressource_cab_id"] = "ref class|CRessourceCab notNull back|plages_cab";
    $props["libelle"]      = "str";
    $props["freq"]         = "time notNull min|00:05:00";
    $props["color"]        = "color";
    $props["_freq"]        = "";
    $props["_repeat"]      = "";
    $props["_type_repeat"] = "enum list|simple|double|triple|quadruple|quintuple|sextuple|septuple|octuple|sameweek";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;

    if ($this->freq == "1:00:00" || $this->freq == "01:00:00") {
      $this->_freq = "60";
    }
    else {
      $this->_freq         = substr($this->freq, 3, 2);
      $this->_freq_minutes = CMbDT::minutesRelative("00:00:00", $this->freq);
    }
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updateFormFields();

    $this->completeField("freq");
    if ($this->_freq !== null) {
      if ($this->_freq == "60") {
        $this->freq = "01:00:00";
      }
      else {
        $this->freq = sprintf("00:%02d:00", $this->_freq);
      }
    }
  }

  /**
   * Chargement des réservations
   *
   * @return CReservation[]
   */
  function loadRefsReservations() {
    return $this->_ref_reservations = $this->loadBackRefs("reservations", "heure");
  }

  /**
   * @return CRessourceCab
   * @throws Exception
   */
  public function loadRefRessource() {
    return $this->_ref_ressource = $this->loadFwdRef("ressource_cab_id");
  }

  function countDuplicatedPlages() {
    $where = array(
      'ressource_cab_id' => "= $this->ressource_cab_id",
      'freq'             => "= '$this->freq'",
      'debut'            => "= '$this->debut'",
      'fin'              => "= '$this->fin'",
      'date'             => "> '$this->date'",
      "WEEKDAY(`date`) = WEEKDAY('$this->date')"
    );

    return $this->_count_duplicated_plages = $this->countList($where);
  }

  function becomeNext() {
    $week_jumped = 0;

    $mapping_repeat = array(
      "octuple"   => 8,
      "septuple"  => 7,
      "sextuple"  => 6,
      "quintuple" => 5,
      "quadruple" => 4,
      "triple"    => 3,
      "double"    => 2,
      "simple"    => 1
    );

    switch ($this->_type_repeat) {
      case "octuple":
      case "septuple":
      case "sextuple":
      case "quintuple":
      case "quadruple":
      case "triple":
      case "double":
      case "simple":
        $ratio = $mapping_repeat[$this->_type_repeat];
        $this->date = CMbDT::date("+$ratio WEEK", $this->date);
        $week_jumped += $ratio;
        break;
      case "sameweek":
        $week_number = CMbDT::weekNumberInMonth($this->date);
        $next_month  = CMbDT::monthNumber(CMbDT::date("+1 MONTH", $this->date));
        $i           = 0;
        do {
          $this->date = CMbDT::date("+1 WEEK", $this->date);
          $week_jumped++;
          $i++;
        } while (
          $i < 10 &&
          (CMbDT::monthNumber($this->date) < $next_month) ||
          (CMbDT::weekNumberInMonth($this->date) != $week_number)
        );
        break;
      default:
        return ++$week_jumped;
    }

    // Stockage des champs modifiés
    $debut   = $this->debut;
    $fin     = $this->fin;
    $freq    = $this->freq;
    $libelle = $this->libelle;
    $color   = $this->color;

    // Recherche de la plage suivante
    $where["date"]    = "= '$this->date'";
    $where["ressource_cab_id"] = "= '$this->ressource_cab_id'";
    $where[]          = "`debut` = '$this->debut' OR `fin` = '$this->fin'";
    if (!$this->loadObject($where)) {
      $this->plage_ressource_cab_id = null;
    }

    // Remise en place des champs modifiés
    $this->debut   = $debut;
    $this->fin     = $fin;
    $this->freq    = $freq;
    $this->libelle = $libelle;

    $this->updateFormFields();

    return $week_jumped;
  }

  function getUtilisation() {
    $utilisation = array();
    $old = $this->debut;

    for ($i = $this->debut; $i < $this->fin; $i = CMbDT::addTime("+" . $this->freq, $i)) {
      if ($old > $i) {
        break;
      }
      $utilisation[$i] = 0;
      $old = $i;
    }

    foreach ($this->_ref_reservations as $_reservation) {
      if (!isset($utilisation[$_reservation->heure])) {
        continue;
      }
      $emplacement = $_reservation->heure;
      for ($i = 0; $i < $_reservation->duree; $i++) {
        if (isset($utilisation[$emplacement])) {
          $utilisation[$emplacement]++;
        }
        $emplacement = CMbDT::addTime("+" . $this->freq, $emplacement);
      }
    }

    return $utilisation;
  }
}
