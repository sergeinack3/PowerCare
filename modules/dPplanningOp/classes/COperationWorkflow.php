<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbDT;
use Ox\Core\CView;

/**
 * Classe des protocoles
 */
class COperationWorkflow extends COperationMiner {
  // Plain fields
  public $date_operation;
  public $date_creation;
  public $date_cancellation;
  public $date_consult_chir;
  public $date_consult_anesth;
  public $date_visite_anesth;
  public $date_creation_consult_chir;
  public $date_creation_consult_anesth;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "operation_workflow";
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["operation_id"]                .= " back|workflow";
    $props["date_operation"]               = "dateTime notNull";
    $props["date_creation"]                = "dateTime";
    $props["date_cancellation"]            = "dateTime";
    $props["date_consult_chir"]            = "dateTime";
    $props["date_consult_anesth"]          = "dateTime";
    $props["date_visite_anesth"]           = "date";
    $props["date_creation_consult_chir"]   = "dateTime";
    $props["date_creation_consult_anesth"] = "dateTime";
    return $props;
  }

  /**
   * @see parent::mine()
   */
  function mine(COperation $operation) {
    parent::mine($operation);

    // to prevent importation logs perturbations (post-event creations)
    static $days_tolerance = 3;

    // to prevent user log queries on masters
    CView::enforceSlave();

    // Operation
    $this->date_operation = $operation->_datetime;
    $log = $operation->loadCreationLog();
    if (CMbDT::daysRelative($operation->_datetime, $log->date) < $days_tolerance) {
      $this->date_creation = $log->date;
    }

    $this->date_visite_anesth = $operation->date_visite_anesth;
    if ($operation->annulee) {
      $log = $operation->loadFirstLogForField("annulee");
      if (CMbDT::daysRelative($operation->_datetime, $log->date) < $days_tolerance) {
        $this->date_cancellation = $log->date;
      }
    }

    // Consult anesthesie
    $dossier = $operation->loadRefsConsultAnesth();
    $consult = $dossier->loadRefConsultation();
    if ($consult->_id) {
      $consult->loadRefPlageConsult();
      $this->date_consult_anesth = $consult->_datetime;
      $log = $consult->loadCreationLog();
      if (CMbDT::daysRelative($consult->_datetime, $log->date) < $days_tolerance) {
        $this->date_creation_consult_anesth = $log->date;
      }
    }

    // Consult chirurgie
    $consult = $operation->loadRefConsultChir();
    if ($consult->_id) {
      $consult->loadRefPlageConsult();
      $this->date_consult_chir = $consult->_datetime;
      $log = $consult->loadCreationLog();
      if (CMbDT::daysRelative($consult->_datetime, $log->date) < $days_tolerance) {
        $this->date_creation_consult_chir = $log->date;
      }
    }

    CView::disableSlave();
  }
}
