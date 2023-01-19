<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;

/**
 * Class TimelineFactory
 */
final class TimelineFactory implements IShortNameAutoloadable {
  /** @var TimelineMenuItem */
  private $menu;
  /** @var string[] */
  private $filtered_menus = [];

  /**
   * Makes a menu item
   *
   * @TODO: this function stays for TAMM but should be removed ASAP (after TAMMs ref)
   *
   * @param string $canonical_name - snake_case menu name
   * @param string $logo           - font awesome (fa fa-logo)
   * @param string $name           - name of the menu actually displayed
   *
   * @return TimelineMenuItem
   */
  public static function makeMenuStatic($canonical_name, $logo, $name) {
    $item = new TimelineMenuItem();
    $item->setCanonicalName($canonical_name);
    $item->setLogo($logo);
    $item->setName(CAppUI::tr($name));
    $item->setSelected(true);

    return $item;
  }

  /**
   * @param array $filtered_menu
   *
   * @return void
   */
  public function setFilteredMenus(array $filtered_menu) {
    $this->filtered_menus = $filtered_menu;
  }

  /**
   * Makes a menu item
   *
   * @param string               $canonical_name - snake_case menu name
   * @param string               $logo           - font awesome (fa fa-logo)
   * @param string               $name           - name of the menu actually displayed
   * @param MenuTimelineCategory $color          - Color of the menu using a category
   * @param ITimelineCategory    $category       - category associated tp the menu. Mandatory nullable argument, this is normal !
   *
   * @return self
   */
  public function makeMenu(
    string $canonical_name,
    string $logo,
    string $name,
    MenuTimelineCategory $color,
    ?ITimelineCategory $category = null
  ): self {
    $this->menu = $this->createMenuItem($canonical_name, $logo, $name, $color, $category);

    if (!$this->filtered_menus) {
      $this->menu->setSelected(true);
    }
    elseif (in_array($this->menu->getCanonicalName(), $this->filtered_menus)) {
      $this->menu->setSelected(true);
    }

    return $this;
  }

  /**
   * @param string                    $canonical_name
   * @param string                    $logo
   * @param string                    $name
   * @param MenuTimelineCategory|null $color
   * @param ITimelineCategory|null    $category
   *
   * @return TimelineMenuItem
   */
  private function createMenuItem(
    string $canonical_name,
    string $logo,
    string $name,
    MenuTimelineCategory $color,
    ?ITimelineCategory $category
  ): TimelineMenuItem {
    $item = new TimelineMenuItem();
    $item->setCanonicalName($canonical_name);
    $item->setLogo($logo);
    $item->setName(CAppUI::tr($name));
    $item->setCategoryColor($color ?? MenuTimelineCategory::NONE());
    if ($category) {
      $item->setTimelineCategory($category);
    }

    return $item;
  }

  /**
   * @param string                    $canonical_name
   * @param string                    $logo
   * @param string                    $name
   * @param MenuTimelineCategory|null $color

   *
   * @return ITimelineAttachableMenuItem
   */
  private function createAttachableMenuItem(
    string $canonical_name,
    string $logo,
    string $name,
    MenuTimelineCategory $color
  ): ITimelineAttachableMenuItem {
    $item = new TimelineAttachedMenuItem();
    $item->setCanonicalName($canonical_name);
    $item->setLogo($logo);
    $item->setName(CAppUI::tr($name));
    $item->setCategoryColor($color ?? MenuTimelineCategory::NONE());

    return $item;
  }

  /**
   * @param string            $canonical_name
   * @param string            $logo
   * @param string            $name
   * @param ITimelineCategory $category
   *
   * @return $this
   */
  public function withChild(string $canonical_name, string $logo, string $name, ITimelineCategory $category): self {
    $item = $this->createMenuItem($canonical_name, $logo, $name, $this->menu->getCategoryColor(), $category);
    // TODO: should this be here ?
    $item->setTimelineCategory($category);
    if (!$this->filtered_menus || in_array($item->getCanonicalName(), $this->filtered_menus)) {
      $item->setSelected(true);
    }

    $this->menu->addChild($item);

    return $this;
  }

  /**
   * @param string            $canonical_name
   * @param string            $logo
   * @param string            $name
   *
   * @return $this
   */
  public function withAttachedChild(string $canonical_name, string $logo, string $name): self {
    $item = $this->createAttachableMenuItem($canonical_name, $logo, $name, $this->menu->getCategoryColor());
    // TODO: should this be here ?

    if (!$this->filtered_menus || in_array($item->getCanonicalName(), $this->filtered_menus)) {
      $item->setSelected(true);
    }

    $item->attachTo($this->menu);

    return $this;
  }

  /**
   * @param bool $visibility
   *
   * @return $this
   */
  public function withVisibility(bool $visibility): self {
    $this->menu->setVisibility($visibility);

    return $this;
  }

  /**
   * @return ITimelineMenuItem
   */
  public function getMenu(): ITimelineMenuItem {
    foreach ($this->menu->getChildren() as $_child) {
      // If the primary menu is selected, the children are also selected
      if ($this->menu->getSelected()) {
        $_child->setSelected(true);
      }

      // If a child is selected, the primary menu is also selected
      if ($_child->getSelected()) {
        $this->menu->setSelected(true);
        break;
      }
    }

    return $this->menu;
  }

  public function setClickable(bool $clickable): self {
    $this->menu->setClickable($clickable);

    return $this;
  }
}
