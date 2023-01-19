<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Exception;
use Ox\Mediboard\Patients\BloodSugarDayAdministrationsReport;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use PHPUnit\Framework\TestCase;

/**
 * Class BloodSugarDayAdministrationsTest
 */
class BloodSugarDayAdministrationsTest extends TestCase {
  /**
   * @test
   * @dataProvider morningAdministrations
   *
   * @param array $administrations
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_morning_administrations(array $administrations, int $expected_count): void {
    $blood_sugar_administrations = new BloodSugarDayAdministrationsReport(new CPrescriptionLine());

    foreach ($administrations as $_administration) {
      $blood_sugar_administrations->add($_administration);
    }

    $this->assertCount($expected_count, $blood_sugar_administrations->getMorning());
  }

  private function data(): array {
    $a1 = new CAdministration();
    $a1->dateTime = "2020-09-01 02:11:11";
    $a2 = new CAdministration();
    $a2->dateTime = "2020-09-01 10:00:00";
    $a3 = new CAdministration();
    $a3->dateTime = "2020-09-01 11:00:00";
    $a4 = new CAdministration();
    $a4->dateTime = "2020-09-01 12:00:00";
    $a5 = new CAdministration();
    $a5->dateTime = "2020-09-01 15:00:00";
    $a6 = new CAdministration();
    $a6->dateTime = "2020-09-01 16:00:00";
    $a7 = new CAdministration();
    $a7->dateTime = "2020-09-01 20:00:00";
    $a8 = new CAdministration();
    $a8->dateTime = "2020-09-01 23:00:00";

    return [$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8];
  }

  public function morningAdministrations(): array {
    [$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8] = $this->data();

    return [
      [[$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8], 2],
      [[$a1, $a3, $a4, $a5, $a6, $a7, $a8], 1],
      [[$a3, $a4, $a5, $a6, $a7, $a8], 0]
    ];
  }

  /**
   * @test
   * @dataProvider middayAdministrations
   *
   * @param array $administrations
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_midday_administrations(array $administrations, int $expected_count): void {
    $blood_sugar_administrations = new BloodSugarDayAdministrationsReport(new CPrescriptionLine());

    foreach ($administrations as $_administration) {
      $blood_sugar_administrations->add($_administration);
    }

    $this->assertCount($expected_count, $blood_sugar_administrations->getMidday());
  }

  public function middayAdministrations(): array {
    [$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8] = $this->data();

    return [
      [[$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8], 2],
      [[$a1, $a2, $a3, $a5, $a6, $a7, $a8], 1],
      [[$a1, $a2, $a5, $a6, $a7, $a8], 0],
    ];
  }

  /**
   * @test
   * @dataProvider afternoonAdministrations
   *
   * @param array $administrations
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_afternoon_administrations(array $administrations, int $expected_count): void {
    $blood_sugar_administrations = new BloodSugarDayAdministrationsReport(new CPrescriptionLine());

    foreach ($administrations as $_administration) {
      $blood_sugar_administrations->add($_administration);
    }

    $this->assertCount($expected_count, $blood_sugar_administrations->getAfternoon());
  }

  public function afternoonAdministrations(): array {
    [$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8] = $this->data();

    return [
      [[$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8], 2],
      [[$a1, $a2, $a3, $a4, $a5, $a7, $a8], 1],
      [[$a1, $a2, $a3, $a4, $a7, $a8], 0],
    ];
  }

  /**
   * @test
   * @dataProvider eveningNightAdministrations
   *
   * @param array $administrations
   * @param int   $expected_count
   *
   * @throws Exception
   */
  public function get_evening_night_administrations(array $administrations, int $expected_count): void {
    $blood_sugar_administrations = new BloodSugarDayAdministrationsReport(new CPrescriptionLine());

    foreach ($administrations as $_administration) {
      $blood_sugar_administrations->add($_administration);
    }

    $this->assertCount($expected_count, $blood_sugar_administrations->getEveningNight());
  }

  public function eveningNightAdministrations(): array {
    [$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8] = $this->data();

    return [
      [[$a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8], 2],
      [[$a1, $a2, $a3, $a4, $a5, $a6, $a7], 1],
      [[$a1, $a2, $a3, $a4, $a5, $a6], 0],
    ];
  }
}
