<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Exception;
use Ox\Mediboard\Patients\BloodSugarDayConstantsReport;
use Ox\Mediboard\Patients\CConstantesMedicales;
use PHPUnit\Framework\TestCase;

/**
 * Class BloodSugarDayConstantsTest
 */
class BloodSugarDayConstantsTest extends TestCase {
  /**
   * @test
   * @dataProvider morningConstants
   *
   * @param array $constants
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_morning_constants(array $constants, int $expected_count): void {
    $blood_sugar_constants = new BloodSugarDayConstantsReport();

    foreach ($constants as $_constant) {
      $blood_sugar_constants->add($_constant);
    }

    $this->assertCount($expected_count, $blood_sugar_constants->getMorning());
  }

  public function morningConstants(): array {
    [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8] = $this->data();

    return [
      [[$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8], 2],
      [[$c1, $c3, $c4, $c5, $c6, $c7, $c8], 1],
      [[$c3, $c4, $c5, $c6, $c7, $c8], 0],
    ];
  }

  private function data(): array {
    $c1           = new CConstantesMedicales();
    $c1->datetime = "2020-09-01 02:11:11";
    $c1->glycemie = 2;
    $c2           = new CConstantesMedicales();
    $c2->datetime = "2020-09-01 10:00:00";
    $c2->glycemie = 2;
    $c3           = new CConstantesMedicales();
    $c3->datetime = "2020-09-01 11:00:00";
    $c3->glycemie = 2;
    $c4           = new CConstantesMedicales();
    $c4->datetime = "2020-09-01 12:00:00";
    $c4->glycemie = 2;
    $c5           = new CConstantesMedicales();
    $c5->datetime = "2020-09-01 15:00:00";
    $c5->glycemie = 2;
    $c6           = new CConstantesMedicales();
    $c6->datetime = "2020-09-01 16:00:00";
    $c6->glycemie = 2;
    $c7           = new CConstantesMedicales();
    $c7->datetime = "2020-09-01 20:00:00";
    $c7->glycemie = 2;
    $c8           = new CConstantesMedicales();
    $c8->datetime = "2020-09-01 23:00:00";
    $c8->glycemie = 2;

    return [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8];
  }

  /**
   * @test
   * @dataProvider middayConstants
   *
   * @param array $constants
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_midday_constants(array $constants, int $expected_count): void {
    $blood_sugar_constants = new BloodSugarDayConstantsReport();

    foreach ($constants as $_constant) {
      $blood_sugar_constants->add($_constant);
    }

    $this->assertCount($expected_count, $blood_sugar_constants->getMidday());
  }

  public function middayConstants(): array {
    [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8] = $this->data();

    return [
      [[$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8], 2],
      [[$c1, $c2, $c3, $c5, $c6, $c7, $c8], 1],
      [[$c1, $c2, $c5, $c6, $c7, $c8], 0],
    ];
  }

  /**
   * @test
   * @dataProvider afternoonConstants
   *
   * @param array $constants
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_afternoon_constants(array $constants, int $expected_count): void {
    $blood_sugar_constants = new BloodSugarDayConstantsReport();

    foreach ($constants as $_constant) {
      $blood_sugar_constants->add($_constant);
    }

    $this->assertCount($expected_count, $blood_sugar_constants->getAfternoon());
  }

  public function afternoonConstants(): array {
    [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8] = $this->data();

    return [
      [[$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8], 2],
      [[$c1, $c2, $c3, $c4, $c5, $c7, $c8], 1],
      [[$c1, $c2, $c3, $c4, $c7, $c8], 0],
    ];
  }

  /**
   * @test
   * @dataProvider eveningNightConstants
   *
   * @param array $constants
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_evening_night_constants(array $constants, int $expected_count): void {
    $blood_sugar_constants = new BloodSugarDayConstantsReport();

    foreach ($constants as $_constant) {
      $blood_sugar_constants->add($_constant);
    }

    $this->assertCount($expected_count, $blood_sugar_constants->getEveningNight());
  }

  public function eveningNightConstants(): array {
    [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8] = $this->data();

    return [
      [[$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8], 2],
      [[$c1, $c2, $c3, $c4, $c5, $c6, $c7], 1],
      [[$c1, $c2, $c3, $c4, $c5, $c6], 0],
    ];
  }

  /**
   * @test
   * @throws Exception
   */
  public function without_blood_sugar_set(): void {
    $c1           = new CConstantesMedicales();
    $c1->datetime = "2020-09-01 02:11:11";
    $c2           = new CConstantesMedicales();
    $c2->datetime = "2020-09-01 10:00:00";

    $blood_sugar_constants = new BloodSugarDayConstantsReport();
    $blood_sugar_constants->add($c1);
    $blood_sugar_constants->add($c2);

    $this->assertCount(0, $blood_sugar_constants->getMorning());
  }
}
