<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbRange;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Description
 */
class CDailySalleOccupation extends CDailySalleMiner {

  public $cumulative_plages_planned;
  public $nb_plages_planned;
  public $nb_plages_planned_valid;

  public $cumulative_real_interventions;
  public $nb_real_interventions;
  public $nb_real_intervention_valid;

  public $cumulative_opened_patient;
  public $nb_interventions_opened_patient;
  public $nb_intervention_opened_patient_valid;

  public $cumulative_plages_minus_interventions;    // temps utilisé en dehors des plages
  // nb element

  public $cumulative_interventions_minus_plages;    // temps opératoires inutilisés


  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "salle_daily_occupation";
    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["salle_id"]                             .= " back|salle_occupations";
    $props["cumulative_plages_planned"]             = "num";    // minutes
    $props["nb_plages_planned"]                     = "num";
    $props["nb_plages_planned_valid"]               = "num";

    $props["cumulative_real_interventions"]         = "num";    // minutes
    $props["nb_real_interventions"]                 = "num";
    $props["nb_real_intervention_valid"]            = "num";

    $props["cumulative_opened_patient"]             = "num";
    $props["nb_interventions_opened_patient"]       = "num";
    $props["nb_intervention_opened_patient_valid"]  = "num";

    $props["cumulative_plages_minus_interventions"] = "num";    // minutes

    $props["cumulative_interventions_minus_plages"] = "num";    // minutes
    return $props;
  }

  /**
   * @see parent::mine
   */
  function mine($salle_id, $date) {
    parent::mine($salle_id, $date);

    // plages
    $plage_op = new CPlageOp();
    $plage_op->date = $date;
    $plage_op->salle_id = $salle_id;
    /** @var CPlageOp[] $plages */
    $plages = $plage_op->loadMatchingList();

    $plages_to_use = array();
    $range_plage = array();
    foreach ($plages as $kp => $_plage) {
      if ($_plage->debut >= $_plage->fin) {
        continue;
      }
      $plages_to_use[$kp] = $_plage;
      CMbRange::union($range_plage, array("lower" => $_plage->debut, "upper" => $_plage->fin));
    }
    $this->nb_plages_planned = count($plages);
    $this->nb_plages_planned_valid = count($plages_to_use);
    $this->cumulative_plages_planned = 0;
    foreach ($range_plage as $_range) {
      $this->cumulative_plages_planned += CMbDT::minutesRelative($_range["lower"], $_range["upper"]);
    }

    // interventions
    $interv = new COperation();
    $interv->salle_id = $salle_id;
    $interv->date = $date;
    /** @var COperation[] $intervs */
    $intervs = $interv->loadMatchingList();

    $interv_to_use = array();
    $range_inter = array();
    foreach ($intervs as $ki => $_interv) {
      // cleanup invalid
      if (!$_interv->entree_salle || !$_interv->sortie_salle || ($_interv->entree_salle >= $_interv->sortie_salle)) {
        continue;
      }

      $interv_to_use[$ki] = $_interv;
      CMbRange::union($range_inter, array("lower" => $_interv->entree_salle, "upper" => $_interv->sortie_salle));
    }
    $this->nb_real_interventions = count($intervs);
    $this->nb_real_intervention_valid = count($interv_to_use);
    $this->cumulative_real_interventions = 0;
    foreach ($range_inter as $_range) {
      $this->cumulative_real_interventions += CMbDT::minutesRelative($_range["lower"], $_range["upper"]);
    }

    // opening patient
    $interv_to_use = array();
    $range_inter_opened = array();
    foreach ($intervs as $ki => $_interv) {
      // cleanup invalid
      if (!$_interv->debut_op || !$_interv->fin_op || ($_interv->debut_op >= $_interv->fin_op)) {
        continue;
      }

      $interv_to_use[$ki] = $_interv;
      CMbRange::union($range_inter_opened, array("lower" => $_interv->debut_op, "upper" => $_interv->fin_op));
    }
    $this->nb_interventions_opened_patient = count($intervs);
    $this->nb_intervention_opened_patient_valid = count($interv_to_use);
    $this->cumulative_opened_patient = 0;
    foreach ($range_inter_opened as $_range) {
      $this->cumulative_opened_patient += CMbDT::minutesRelative($_range["lower"], $_range["upper"]);
    }

    // range operation
    $this->cumulative_plages_minus_interventions = 0;
    $plages_minus_interv = CMbRange::multiCrop($range_plage, $range_inter);
    foreach ($plages_minus_interv as $_plage) {
      $this->cumulative_plages_minus_interventions = CMbDT::minutesRelative($_plage["lower"], $_plage["upper"]);
    }

    $this->cumulative_interventions_minus_plages = 0;
    $interv_minus_plage = CMbRange::multiCrop($range_inter, $range_plage);
    foreach ($interv_minus_plage as $_plage) {
      $this->cumulative_interventions_minus_plages = CMbDT::minutesRelative($_plage["lower"], $_plage["upper"]);
    }

    return $this;
  }

}
