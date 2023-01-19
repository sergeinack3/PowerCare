<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbArray;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Preparation of rooms
 */
class PreparationSalle {
  /** @var CSalle[] */
  public $salles = [];

  /** @var int */
  public $total = 0;

  /** @var int */
  public $total_horsplage = 0;

  /**
   * PreparationSalle constructor.
   *
   * @param COperation[] $operations
   *
   * @throws CMbModelNotFoundException
   */
  public function __construct(array $operations) {
    foreach ($operations as $_operation) {
      $this->addOperation($_operation);
    }
  }

  /**
   * Add operation
   *
   * @param COperation $operation
   *
   * @return void
   * @throws CMbModelNotFoundException
   */
  private function addOperation(COperation $operation): void {
    $salle = $operation->_ref_salle;

    if (!$salle) {
      throw new CMbModelNotFoundException("common-error-Object not found");
    }

    if (!isset($this->salles[$salle->_id])) {
      $this->salles[$salle->_id] = $salle;
    }

    $this->salles[$salle->_id]->_ref_operations[] = $operation;

    $this->total++;

    if (!$salle->_id) {
      $this->total_horsplage++;
    }
  }

  /**
   * Sort the operations by time
   *
   * @return void
   */
  public function sortByTime(): void {
    foreach ($this->salles as $_salle_id => $_salle) {
      CMbArray::pluckSort($this->salles[$_salle_id]->_ref_operations, SORT_ASC, "_datetime_best");
    }
  }
}
