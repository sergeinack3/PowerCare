<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTime;
use Exception;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescriptionLine;

/**
 * Class BloodSugarDayAdministrations
 *
 * Administrations which are related to the blood's sugar patient
 */
class BloodSugarDayAdministrationsReport {
  /** @var CPrescriptionLine */
  private $prescription_line;

  /** @var CAdministration[] */
  private $morning = [];

  /** @var CAdministration[] */
  private $midday = [];

  /** @var CAdministration[] */
  private $afternoon = [];

  /** @var CAdministration[] */
  private $evening_night = [];

  /**
   * BloodSugarDay constructor.
   *
   * @param CPrescriptionLine $prescription_line
   */
  public function __construct(CPrescriptionLine $prescription_line) {
    $this->prescription_line = $prescription_line;
  }

  /**
   * @return CPrescriptionLine
   */
  public function getPrescriptionLine(): CPrescriptionLine {
    return $this->prescription_line;
  }

  /**
   * @return CAdministration[]
   */
  public function getMorning(): array {
    return $this->morning;
  }

  /**
   * @return CAdministration[]
   */
  public function getMidday(): array {
    return $this->midday;
  }

  /**
   * @return CAdministration[]
   */
  public function getAfternoon(): array {
    return $this->afternoon;
  }

  /**
   * @return CAdministration[]
   */
  public function getEveningNight(): array {
    return $this->evening_night;
  }

  /**
   * @param CAdministration $_administration
   *
   * @return void
   * @throws Exception
   */
  public function add(CAdministration $_administration): void {
    $hour = (new DateTime($_administration->dateTime))->format('H');

    if (!$hour) {
      throw new Exception('Bad hour format');
    }

    if ($hour >= 0 && $hour < 11) {
      $this->morning[] = $_administration;
    }
    elseif ($hour >= 11 && $hour < 15) {
      $this->midday[] = $_administration;
    }
    elseif ($hour >= 15 && $hour < 20) {
      $this->afternoon[] = $_administration;
    }
    else {
      $this->evening_night[] = $_administration;
    }
  }
}