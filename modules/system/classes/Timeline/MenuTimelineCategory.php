<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use MyCLabs\Enum\Enum;

/**
 * Menu timeline category enumeration
 *
 * @method static static NONE()
 * @method static static APPOINTMENTS()
 * @method static static ADDICTOLOGY()
 * @method static static DOCUMENTS()
 * @method static static MEDICAL()
 * @method static static SURGERY()
 * @method static static PREGNANCY()
 * @method static static BIRTH()
 * @method static static STAY()
 * @method static static OTHER()
 */
class MenuTimelineCategory extends Enum {
  private const NONE = 'none';

  private const APPOINTMENTS = 'appointments';
  private const ADDICTOLOGY = 'addictology';
  private const DOCUMENTS = 'documents';
  private const MEDICAL = 'medical';
  private const SURGERY = 'surgery';
  private const BIRTH = 'birth';
  private const PREGNANCY = 'pregnancy';
  private const STAY = 'stay';
  private const OTHER = 'other';
}
