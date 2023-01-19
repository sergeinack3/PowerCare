<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc\Tests\Unit;

use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Bloc\PreparationSalle;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

/**
 * Class PreparationSalleTest
 */
class PreparationSalleTest extends OxUnitTestCase {
  /**
   * @param COperation[] $operations
   * @param mixed[]      $expected_result
   *
   * @dataProvider operationProvider
   * @throws CMbModelNotFoundException
   */
  public function testGetTotalFromOperations(array $operations, array $expected_result) {
    $this->assertEquals($expected_result["total"], (new PreparationSalle($operations))->total);
  }

  /**
   * @param COperation[] $operations
   * @param mixed[]      $expected_result
   *
   * @dataProvider operationProvider
   * @throws CMbModelNotFoundException
   */
  public function testSalle(array $operations, array $expected_result) {
    $this->assertArrayHasKey($expected_result["salle_id"], (new PreparationSalle($operations))->salles);
  }

  /**
   * @param COperation[] $operations
   * @param mixed[]      $expected_result
   *
   * @dataProvider operationProvider
   * @throws CMbModelNotFoundException
   */
  public function testGetTotalHorsPlage(array $operations, array $expected_result) {
    $this->assertEquals($expected_result["total_horsplage"], (new PreparationSalle($operations))->total_horsplage);
  }

  /**
   *
   * @throws CMbModelNotFoundException
   */
  public function testExceptionSalleNotFound() {
    $operation = new COperation();

    $this->expectException(CMbModelNotFoundException::class);

    new PreparationSalle([$operation]);
  }

  /**
   * @return array[]
   */
  public function operationProvider() {
    $salle      = new CSalle();
    $salle->_id = 1;

    $interv_in_plage             = new COperation();
    $interv_in_plage->_ref_salle = $salle;

    $interv_hors_plage             = new COperation();
    $interv_hors_plage->_ref_salle = new CSalle();

    return [
      "interv_in_plage"                => [
        [$interv_in_plage],
        [
          "salle_id"        => $interv_in_plage->_ref_salle->_id,
          "total"           => 1,
          "total_horsplage" => 0
        ],
      ],
      "interv_hors_plage"              => [
        [$interv_hors_plage],
        [
          "salle_id"        => "",
          "total"           => 1,
          "total_horsplage" => 1
        ]
      ],
      "interv_in_plage_and_hors_plage" => [
        [$interv_in_plage, $interv_hors_plage],
        [
          "salle_id"        => "",
          "total"           => 2,
          "total_horsplage" => 1
        ]
      ]
    ];
  }

  /**
   * Test planning sorted by time
   *
   * @throws CMbModelNotFoundException
   */
  public function testSortByTime() {
    $salle      = new CSalle();
    $salle->_id = 1;

    $interv1                 = new COperation();
    $interv1->operation_id   = 1;
    $interv1->_ref_salle     = $salle;
    $interv1->_datetime_best = "08:00:00";

    $interv2                 = new COperation();
    $interv2->operation_id   = 2;
    $interv2->_ref_salle     = $salle;
    $interv2->_datetime_best = "07:00:00";

    $planning = new PreparationSalle([$interv1, $interv2]);
    $planning->sortByTime();

    $salle_expected      = new CSalle();
    $salle_expected->_id = 1;

    $salle_expected->_ref_operations = [1 => $interv2, 0 => $interv1];

    $this->assertEquals([1 => $salle_expected], $planning->salles);
  }
}
