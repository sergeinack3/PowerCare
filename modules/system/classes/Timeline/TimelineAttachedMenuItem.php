<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

/**
 * Class TimelineAttachedMenuItem
 */
class TimelineAttachedMenuItem extends TimelineMenuItem implements ITimelineMenuItem, ITimelineAttachableMenuItem {
  /** @var ITimelineMenuItem */
  private $attachTo;

  /**
   * @inheritDoc
   */
  public function isVisible(): bool {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function getChildren(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function attachTo(ITimelineMenuItem $menu_item) {
    $this->attachTo = $menu_item;
    $menu_item->attachMenu($this);
  }

  /**
   * @inheritDoc
   */
  public function isAttached() {
    return (bool)$this->attachTo;
  }

  /**
   * @inheritDoc
   */
  public function attachedTo() {
    return $this->attachTo;
  }
}