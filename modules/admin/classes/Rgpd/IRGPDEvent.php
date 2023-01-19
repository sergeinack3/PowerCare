<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;
/**
 * Description
 */
interface IRGPDEvent {
  /**
   * Get RGPD context
   *
   * @return IRGPDCompliant
   */
  public function getRGPDContext();

  /**
   * Tells if the event triggers a treatment
   *
   * @param boolean $first_store Is it the first storing of the object?
   *
   * @return bool
   */
  public function checkTrigger($first_store = false);

  /**
   * Triggers the event treatment
   *
   * @return void
   */
  public function triggerEvent();

  /**
   * Get the CGroups ID for RGPD event
   *
   * @return int|null
   */
  public function getGroupID();
}
