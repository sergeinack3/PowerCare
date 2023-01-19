<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Class TimelineMenuItem
 */
class TimelineMenuItem implements ITimelineMenuItem, IShortNameAutoloadable
{
    /** @var string */
    private $name;

    /** @var string */
    private $logo;

    /** @var MenuTimelineCategory */
    private $color;

    /** @var string */
    private $canonical_name;

    /** @var ITimelineMenuItem[] */
    private $children = [];

    /** @var ITimelineCategory */
    private $category;

    /** @var bool */
    private $visibility = true;

    /** @var bool */
    private $clickable = true;

    /** @var ITimelineAttachableMenuItem[] */
    private $attached_menus = [];

    /** @var bool */
    private $selected;

    /**
     * @return array
     */
    public function getInvolvedUsers(): array
    {
        if (!$this->category) {
            return [];
        }
        if (!$this->selected) {
            return [];
        }

        return $this->category->getInvolvedUsers();
    }

    /**
     * @return int
     */
    public function getAmountEvents(): int
    {
        if (!$this->category) {
            return 0;
        }
        if (!$this->selected) {
            return 0;
        }

        return $this->category->getAmountEvents();
    }

    /**
     * @inheritDoc
     */
    public function getEventsByDate(): array
    {
        if (!$this->category) {
            return [];
        }
        if (!$this->selected) {
            return [];
        }

        return $this->category->getEventsByDate();
    }

    /**
     * @inheritDoc
     */
    public function getEventsByDateTime(): array
    {
        if (!$this->category) {
            return [];
        }
        if (!$this->selected) {
            return [];
        }

        return $this->category->getEventsByDateTime();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * @inheritDoc
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryColor(): MenuTimelineCategory
    {
        return $this->color;
    }

    /**
     * Get the value of the category color
     *
     * @return string
     */
    public function getCategoryColorValue(): string
    {
        return strtolower($this->color->getValue());
    }

    /**
     * @inheritDoc
     */
    public function setCategoryColor(MenuTimelineCategory $color): void
    {
        $this->color = $color;
    }

    /**
     * @inheritDoc
     */
    public function getCanonicalName(): string
    {
        return $this->canonical_name;
    }

    /**
     * @inheritDoc
     */
    public function setCanonicalName(string $canonical_name): void
    {
        $this->canonical_name = $canonical_name;
    }

    /**
     * @return bool
     */
    public function isClickable(): bool
    {
        return $this->clickable;
    }

    /**
     * @param bool $clickable
     */
    public function setClickable(bool $clickable): void
    {
        $this->clickable = $clickable;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return $this->children ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setChildren(ITimelineMenuItem ...$items): void
    {
        $this->children = $items;
    }

    /**
     * @param ITimelineMenuItem $item
     *
     * @return void
     */
    public function addChild(ITimelineMenuItem $item): void
    {
        $this->children[] = $item;
    }

    /**
     * @return int
     */
    public function countChildren(): int
    {
        return count($this->children);
    }

    /**
     * @inheritDoc
     */
    public function setVisibility(bool $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @inheritDoc
     */
    public function isVisible(): bool
    {
        return $this->visibility;
    }

    /**
     * @inheritDoc
     */
    public function setTimelineCategory(ITimelineCategory $category): void
    {
        $this->category = $category;
    }

    /**
     * @inheritDoc
     */
    public function getTimelineCategory(): ITimelineCategory
    {
        return $this->category;
    }

    /**
     * Attach an attachable menu to the primary one attache
     *
     * @param ITimelineAttachableMenuItem $item - the menu attached to this primary one
     *
     * @return void
     */
    public function attachMenu(ITimelineAttachableMenuItem $item): void
    {
        $this->attached_menus[] = $item;
    }

    /**
     * Return attached menus to the primary one
     *
     * @return ITimelineAttachableMenuItem[]
     */
    public function attachedMenus(): array
    {
        return $this->attached_menus;
    }

    /**
     * @inheritDoc
     */
    public function setSelected(bool $selected): void
    {
        $this->selected = $selected;
    }

    /**
     * @inheritDoc
     */
    public function getSelected(): bool
    {
        return $this->selected ?? false;
    }
}
