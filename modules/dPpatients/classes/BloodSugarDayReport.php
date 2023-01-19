<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTimeImmutable;

/**
 * Class BloodSugarDay
 *
 * Represents a blood sugar report of a day
 * It contains a date, constants (divided in periods of the day), administrations (divided in periods of the day)
 */
class BloodSugarDayReport {
  /** @var DateTimeImmutable */
  private $date;

  /** @var BloodSugarDayAdministrationsReport[] */
  private $administrations;

  /** @var BloodSugarDayConstantsReport */
  private $constants;

  /**
   * BloodSugarDay constructor.
   *
   * @param DateTimeImmutable            $date
   * @param BloodSugarDayConstantsReport $constants
   * @param array                        $administrations
   */
  public function __construct(DateTimeImmutable $date, BloodSugarDayConstantsReport $constants, array $administrations) {
    $this->date            = $date;
    $this->constants       = $constants;
    $this->administrations = $administrations;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getDate(): DateTimeImmutable {
    return $this->date;
  }

  /**
   * @return string
   */
  public function getDateString(): string {
    return $this->date->format('Y-m-d');
  }

  /**
   * @return BloodSugarDayAdministrationsReport[]
   */
  public function getAdministrations(): array {
    return $this->administrations;
  }

  /**
   * @return BloodSugarDayConstantsReport
   */
  public function getConstants(): BloodSugarDayConstantsReport {
    return $this->constants;
  }
}
