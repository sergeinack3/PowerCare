<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Ox\Core\CMbException;

/**
 * Class TimelineException
 */
final class TimelineException extends CMbException {
  /**
   * @return CMbException
   */
  public static function menuDoesntExist(): TimelineException {
    return new static('The timeline menu doesn\'t exist');
  }

  /**
   * @return CMbException
   */
  public static function menuAlreadyExists(): TimelineException {
    return new static('The timeline menu already exists');
  }
}