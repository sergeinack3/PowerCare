<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Gestion des redons
 */
class CRedon extends CMbObject {
  /** @var integer Primary key */
  public $redon_id;

  // DB fields
  public $sejour_id;
  public $constante_medicale;
  public $actif;
  public $sous_vide;
  public $date_pose;
  public $date_retrait;

  // References
  /** @var CSejour */
  public $_ref_sejour;

  /** @var CReleveRedon[] */
  public $_ref_releves = [];

  /** @var CReleveRedon */
  public $_ref_last_releve;

  // Form fields
  public $_class_redon;
  public $_qte_cumul;

  static $list = [
    "redon" => [
      "redon", "redon_2", "redon_3", "redon_4", "redon_5", "redon_6",
      "redon_7", "redon_8", "redon_9", "redon_10", "redon_11", "redon_12",
      ],
    "redon_accordeon" => [
      "redon_accordeon_1", "redon_accordeon_2", "redon_accordeon_3",
      "redon_accordeon_4", "redon_accordeon_5", "redon_accordeon_6",
      ],
    "scurasil" => [
      "scurasil_1", "scurasil_2",
      ],
    "drain" => [
      "drain_1", "drain_2", "drain_3",
      ],
    "lame" => [
      "lame_1", "lame_2", "lame_3",
      ],
    "drain_orifice" => [
      "drain_orifice_1", "drain_orifice_2", "drain_orifice_3", "drain_orifice_4",
      ],
    "drain_pleural" => [
      "drain_pleural_1", "drain_pleural_2", "drain_pleural_3", "drain_pleural_4",
      ],
    "drain_thoracique" => [
      "drain_thoracique_1", "drain_thoracique_2", "drain_thoracique_3", "drain_thoracique_4",
      ],
    "drain_mediastinal" => [
      "drain_mediastinal"
      ]
  ];

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "redon";
    $spec->key   = "redon_id";
    $spec->uniques["redon"] = ["sejour_id", "constante_medicale"];
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["sejour_id"]          = "ref class|CSejour back|redons";
    $props["constante_medicale"] = "enum list|" .
      implode("|", array_map(function($_cst) { return implode("|", $_cst); }, static::$list)) . " notNull";
    $props["actif"]              = "bool default|1";
    $props["sous_vide"]          = "bool";
    $props["date_pose"]          = "dateTime notNull";
    $props["date_retrait"]       = "dateTime moreThan|date_pose";
    $props["_qte_cumul"]         = "num";
    return $props;
  }

  /**
   * @inheritDoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    foreach (static::$list as $_redons) {
      if ($this->_class_redon = array_search($this->constante_medicale, $_redons)) {
         break;
      }
    }

    $this->_view = CAppUI::tr("CRedon.constante_medicale." . $this->constante_medicale);
  }

  /**
   * @inheritDoc
   */
  function store() {
    if (!$this->_id) {
      $this->date_pose = "now";
    }

    return parent::store();
  }

  /**
   * Charge le séjour associé au redon
   *
   * @return CSejour
   * @throws Exception
   */
  public function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  public function loadRefsReleves() {
    return $this->_ref_releves = $this->loadBackRefs("releves", "date DESC");
  }

  public function loadRefLastReleve() {
    $this->loadRefsReleves();

    if (count($this->_ref_releves)) {
      $this->_ref_last_releve = reset($this->_ref_releves);
    }
    else {
      $this->_ref_last_releve           = new CReleveRedon();
      $this->_ref_last_releve->redon_id = $this->_id;
    }

    return $this->_ref_last_releve;
  }

  public function loadRefPrevReleve(CReleveRedon $curr_releve): CReleveRedon
  {
      if (!$curr_releve->_id) {
          return $this->loadRefLastReleve();
      }

      $releves = $this->loadRefsReleves();

      foreach ($releves as $_releve) {
          if ($_releve->_id === $curr_releve->_id) {
              continue;
          }

          if ($_releve->_id < $curr_releve->_id) {
              return $_releve;
          }
      }

      return new CReleveRedon();
  }

  public function loadRefNextReleve(CReleveRedon $curr_releve): CReleveRedon
  {
        if (!$curr_releve->_id) {
            return new CReleveRedon();
        }

        $releves = $this->loadRefsReleves();

        foreach ($releves as $_releve) {
            if ($_releve->_id === $curr_releve->_id) {
                continue;
            }

            if ($_releve->_id > $curr_releve->_id) {
                return $_releve;
            }
        }

        return new CReleveRedon();
    }

  public function getQteCumul() {
    $sejour = $this->loadRefSejour();

    if (!$this->_ref_last_releve) {
      $this->loadRefLastReleve();
    }

    list($constante, $list_datetimes, $list_contexts) = CConstantesMedicales::getFor(
      $sejour->patient_id,
      $this->_ref_last_releve->date ? : CMbDT::dateTime(),
      [$this->constante_medicale],
      $sejour
    );

    return $this->_qte_cumul = $constante->{"_" . $this->constante_medicale . "_cumul"};
  }

  public static function isRedon($cste) {
    foreach (self::$list as $_cstes) {
      if (in_array($cste, $_cstes)) {
        return true;
      }
    }

    return false;
  }
}
