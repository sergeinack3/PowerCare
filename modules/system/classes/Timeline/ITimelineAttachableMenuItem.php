<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

/**
 * Interface ITimelineAttachedMenuItem
 *
 * Defines how to attach a menu item
 * Every action made on the attached category will be also affected to this item
 * (e.g. filter menus on the primary menu (P1) will also display categories of attached menus (submenus) (A1))
 */
interface ITimelineAttachableMenuItem extends ITimelineMenuItem {
  /**
   * Sets the menu item to attach
   * This method must be called on the 'attached menu 2'
   *
   * @param ITimelineMenuItem $menu_item - canonical name of the attached menu
   *
   * @return void
   */
  public function attachTo(ITimelineMenuItem $menu_item);

  /**
   * Is the menu attached to an other menu
   *
   * @return bool
   */
  public function isAttached();

  /**
   * Returns the menu on which it's attached
   *
   * @return ITimelineMenuItem
   */
  public function attachedTo();
}