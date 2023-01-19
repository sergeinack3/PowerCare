<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

/**
 * Interface ITimelineMenuItem
 *
 * TODO: remove some unnecessary methods which are implicit
 * TODO: e.g.: only put getters and the implementation should deal the setting how he wants (setter, constructor ...)
 * TODO: this is to discuss
 */
interface ITimelineMenuItem
{
    /**
     * Sets the name of the menu
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Sets the logo class name
     *
     * @param string $logo
     *
     * @return void
     */
    public function setLogo(string $logo): void;

    /**
     * Sets the background color of the menu item
     *
     * @param MenuTimelineCategory $color
     *
     * @return void
     */
    public function setCategoryColor(MenuTimelineCategory $color): void;

    /**
     * Sets the unique name of the menu (id transmitted by form)
     *
     * @param string $name
     *
     * @return void
     */
    public function setCanonicalName(string $name): void;

    /**
     * Returns the name of the menu
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the class logo of the menu
     *
     * @return string
     */
    public function getLogo(): string;

    /**
     * Returns the background color of the menu item
     *
     * @return MenuTimelineCategory
     */
    public function getCategoryColor(): MenuTimelineCategory;

    /**
     * Returns the unique, canonical name of the menu item
     *
     * @return string
     */
    public function getCanonicalName(): string;

    /**
     * Sets the visibility of the menu item
     *
     * @param bool $visibility
     *
     * @return void
     */
    public function setVisibility(bool $visibility): void;

    /**
     * Gets the visibility of the menu item
     *
     * @return bool
     */
    public function isVisible(): bool;

    /**
     * Sets the sub-menu items
     *
     * @param ITimelineMenuItem ...$items
     *
     * @return void
     */
    public function setChildren(ITimelineMenuItem ...$items): void;

    /**
     * Returns children of the menu item
     *
     * @return ITimelineMenuItem[]
     */
    public function getChildren(): array;

    /**
     * Count the amount of children in the submenu.
     * The value counts children AND attached menus
     *
     * @return int
     */
    public function countChildren(): int;

    /**
     * Sets the category linked to the menu which will retrieve the data
     *
     * @param ITimelineCategory $category
     *
     * @return void
     */
    public function setTimelineCategory(ITimelineCategory $category): void;

    /**
     * Returns the category of a timeline menu
     *
     * @return ITimelineCategory
     */
    public function getTimelineCategory(): ITimelineCategory;

    /**
     * Adds a menu to the list of attached menus
     *
     * @param ITimelineAttachableMenuItem $item
     *
     * @return void
     */
    public function attachMenu(ITimelineAttachableMenuItem $item): void;

    /**
     * Get list of attached menus
     *
     * @return ITimelineAttachableMenuItem[]
     */
    public function attachedMenus(): array;

    /**
     * @return array
     */
    public function getEventsByDate(): array;

    /**
     * @return array
     */
    public function getEventsByDateTime(): array;

    /**
     * @param bool $selected
     *
     * @return void
     */
    public function setSelected(bool $selected): void;

    /**
     * @return bool
     */
    public function getSelected(): bool;
}
