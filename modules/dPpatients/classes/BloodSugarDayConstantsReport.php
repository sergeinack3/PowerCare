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

/**
 * Class BloodSugarDayConstants
 *
 * Represents all blood sugar constants of a day
 */
class BloodSugarDayConstantsReport {
  /** @var CConstantesMedicales[] */
  private $morning = [];

  /** @var CConstantesMedicales[] */
  private $midday = [];

  /** @var CConstantesMedicales[] */
  private $afternoon = [];

  /** @var CConstantesMedicales[] */
  private $evening_night = [];

  /** @var string|null */
  private $blood_sugar_unit = null;

  /**
   * @return CConstantesMedicales[]
   */
  public function getMorning(): array {
    return $this->morning;
  }

  /**
   * @return CConstantesMedicales[]
   */
  public function getMidday(): array {
    return $this->midday;
  }

  /**
   * @return CConstantesMedicales[]
   */
  public function getAfternoon(): array {
    return $this->afternoon;
  }

  /**
   * @return CConstantesMedicales[]
   */
  public function getEveningNight(): array {
    return $this->evening_night;
  }

  /**
   * @return string
   */
  public function getBloodSugarUnit(): string {
    return $this->blood_sugar_unit ?? CConstantesMedicales::$list_constantes['glycemie']['unit'];
  }

  /**
   * @param CConstantesMedicales $constant
   *
   * @return void
   * @throws Exception
   */
  public function add(CConstantesMedicales $constant): void {
    if (!$constant->glycemie) {
      return;
    }

    if (!$this->blood_sugar_unit) {
      $this->blood_sugar_unit = $constant->_unite_glycemie;
    }

    $hour = (new DateTime($constant->datetime))->format('H');

    if (!$hour) {
      throw new Exception('Bad hour format');
    }

    if ($hour >= 0 && $hour < 11) {
      $this->morning[] = $constant;
    }
    elseif ($hour >= 11 && $hour < 15) {
      $this->midday[] = $constant;
    }
    elseif ($hour >= 15 && $hour < 20) {
      $this->afternoon[] = $constant;
    }
    else {
      $this->evening_night[] = $constant;
    }
  }
}