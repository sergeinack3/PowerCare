<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Timeline;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Timeline\ITimelineCategory;

/**
 * Class TimelineFactorySoins
 */
abstract class TimelineFactorySoins implements IShortNameAutoloadable {
  /** @var CPatient - patient involved */
  public static $patient;
  /** @var CSejour */
  public static $stay;

  /**
   * Hydrates a timeline category
   *
   * @param ITimelineCategory $category
   * @param array             $users - practitioners filter (e.g. only Dr Thingy)
   *
   * @return ITimelineCategory
   */
  public static function makeCategory(ITimelineCategory $category, array $users) {
    $category->setPatient(TimelineFactorySoins::$patient);
    $category->setUsers($users);
    $category->setStay(TimelineFactorySoins::$stay);

    return $category;
  }
}
