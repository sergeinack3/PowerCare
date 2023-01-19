<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;

/**
 * TimelineMenu helper functions
 */
final class TimelineMenu implements IShortNameAutoloadable {
    public const ACTION_FILE_PATH_ADDICTOLOGIE = "/modules/addictologie/templates/timeline/actions";

  /** @var ITimelineMenuItem[] */
  private $menu_instances;
  /** @var string[] */
  private $menu_classes;
  /** @var string[] */
  private $selected_canonical_menus;
  /** @var string[] */
  private $glowing_menus;

  /**
   * TimelineMenu constructor.
   *
   * @param ITimelineMenuItem ...$menu_items - accepts null values and are automatically removed
   *
   * @throws CMbException
   */
  public function __construct(?ITimelineMenuItem ...$menu_items) {
    $this->menu_instances = [];

    $item_names = [];
    foreach (array_filter($menu_items) as $_item) {
      if (in_array($_item->getCanonicalName(), $item_names)) {
        throw TimelineException::menuAlreadyExists();
      }
      $item_names[] = $_item->getCanonicalName();
      $this->menu_instances[] = $_item;
    }
  }

  /**
   * @return ITimelineMenuItem[]
   */
  public function getMenuInstances(): array {
    return $this->menu_instances;
  }

  /**
   * Set selected menus using canonical names
   *
   * @param string[] $selected_menus
   *
   * @return void
   */
  public function setSelectedMenus(array $selected_menus): void {
    if (count($selected_menus) === 0) {
      foreach ($this->menu_instances as $_item) {
        $selected_menus[] = $_item->getCanonicalName();

        foreach ($_item->getChildren() as $_child) {
          $selected_menus[] = $_child->getCanonicalName();
        }
      }
    }

    $this->selected_canonical_menus = (count($selected_menus) > 0) ? $selected_menus : $this->makeMapMenu();
  }

  /**
   * Make a menu map with the key being the parent name of the menu and the values being the children names
   *
   * @return ITimelineMenuItem[]
   */
  private function makeMapMenu(): array {
    foreach ($this->menu_instances as $_menu) {
      if ($_menu instanceof ITimelineMenuItem) {
        $key_name       = $_menu->getCanonicalName();
        $map[$key_name] = [];

        // Primary menu
        $map[$key_name][] = $_menu->getCanonicalName();

        foreach ($_menu->getChildren() as $_child) {
          $map[$key_name][]                 = $_child->getCanonicalName();
          $map[$_child->getCanonicalName()] = [$_child->getCanonicalName()];
        }
      }
    }

    return $map;
  }

  /**
   * @return string[]
   */
  public function getMenusGlowing(): array {
    return $this->glowing_menus ?? [];
  }

  /**
   * Add a category to the menu
   * Will be added only if the menu is considered selected (cf. setSelectedMenu())
   *
   * @param ITimelineMenuItem $menu
   * @param ITimelineCategory $category
   *
   * @return bool
   * @throws CMbException
   */
  public function addCategory(ITimelineMenuItem $menu, ITimelineCategory $category): bool {
    if (count(CMbArray::searchRecursive($menu->getCanonicalName(), $this->makeMapMenu())) === 0) {
      return false;
    }

    $parent_name = $this->getParentNameMenuInMap($menu);

    if ($parent_name) {
      // Add the parent to glowing menu
      $this->glowing_menus[] = $parent_name;
      // Add the menu to classes
      $this->menu_classes[] = get_class($menu);
      // Get the instance to which the category will be added
      $menu_instance = $this->getMenuInstanceFromName($menu->getCanonicalName());
      $menu_instance->setTimelineCategory($category);

      return true;
    }

    return false;
  }

  /**
   * Get the canonical parent name of a menu
   *
   * @param ITimelineMenuItem $menu
   *
   * @return string|null
   */
  private function getParentNameMenuInMap(ITimelineMenuItem $menu): ?string {
    foreach ($this->makeMapMenu() as $parent_name => $children_names) {
      // Look for target in parent menus
      if ($parent_name === $menu->getCanonicalName() && in_array($parent_name, $this->getSelectedMenus())) {
        return $parent_name;
      }

      // Look for the target in children's menus
      foreach ($children_names as $child_name) {
        if ($child_name === $menu->getCanonicalName() && in_array($child_name, $this->getSelectedMenus())) {
          return $parent_name;
        }
      }
    }

    return null;
  }

  /**
   * @return string[]
   */
  public function getSelectedMenus(): array {
    return $this->selected_canonical_menus ?? [];
  }

  /**
   * Get an instance of current list using a canonical name
   *
   * @param string $name
   *
   * @return ITimelineMenuItem
   * @throws CMbException
   */
  private function getMenuInstanceFromName(string $name): ITimelineMenuItem {
    $selected_instance = null;

    foreach ($this->menu_instances as $menu_instance) {
      if ($menu_instance->getCanonicalName() === $name) {
        $selected_instance = $menu_instance;
        break;
      }

      foreach ($menu_instance->getChildren() as $child) {
        if ($child->getCanonicalName() === $name) {
          $selected_instance = $child;
          break;
        }
      }
    }

    if (!$selected_instance) {
      throw TimelineException::menuDoesntExist();
    }

    return $selected_instance;
  }

  /**
   * Gets all menus associated to primary menus
   *
   * @return ITimelineMenuItem[]
   */
  public function getClasses() {
    $classes = $this->menu_instances;

    foreach ($this->menu_instances as $_menu) {
      if ($_menu instanceof ITimelineMenuItem && !$_menu instanceof ITimelineAttachableMenuItem) {
        foreach ($_menu->getChildren() as $_child) {
          $classes[] = $_child;

          // @TODO: recursive call
          foreach ($_child->attachedMenus() as $_attached) {
            $classes[] = $_attached;
          }
        }
        foreach ($_menu->attachedMenus() as $_attached) {
          $classes[] = $_attached;
        }
      }
    }

    return $classes;
  }
}
