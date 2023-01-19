<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class Timeline
 */
class Timeline implements IShortNameAutoloadable
{
    /** @var */
    private $timeline = [];

    /** @var int[] */
    private $badges = [];

    /** @var CMediusers[] */
    private $involved_users = [];

    /** @var ITimelineMenuItem[] */
    private $menu_items = [];

    /** @var string */
    private $scale = "date";

    /**
     * CTimelineCabinet constructor.
     *
     * @param ITimelineMenuItem[] $menu_items
     */
    public function __construct(array $menu_items = [])
    {
        $this->menu_items = $menu_items;
    }

    /**
     * @return CStoredObject[]
     */
    public function getTimeline()
    {
        return $this->timeline;
    }

    /**
     * @return int[]
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * @return CMediusers[]
     */
    public function getInvolvedUsers()
    {
        return $this->involved_users;
    }

    /**
     * @return ITimelineMenuItem[]
     */
    public function getMenuItems()
    {
        return $this->menu_items;
    }

    /**
     * @return string
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param $scale
     * @return mixed
     */
    public function setScale($scale)
    {
        return $this->scale = $scale;
    }

    /**
     * Move the Document element to the end of the array
     * To properly order the documents after the contexts
     * @return ITimelineMenuItem[]
     */
    public function getMenuAfterMoveDocumentToTheBack(): array
    {
        $menu = $this->menu_items;
        $indexDocument = null;
        foreach ($menu as $k => $m) {
            if ($m->getName() === "Document") {
                $indexDocument = $k;
            }
        }

        if (!is_null($indexDocument)) {
            $document = array_slice($menu, $indexDocument, 1);
            if (!empty($document)) {
                array_splice($menu, $indexDocument, 1);
                $menu[] = $document[0];
            }
        }

        return $menu;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function buildTimeline()
    {
        $withTime = $this->getScale() === "time";
        $menu = ($withTime ? $this->getMenuAfterMoveDocumentToTheBack() : $this->menu_items);

        foreach ($menu as $_menu_item) {
            $_events = ($withTime ? $_menu_item->getEventsByDateTime() : $_menu_item->getEventsByDate());

            if ($_events) {
                $this->mergeTimeline($_events);
            }

            $this->mergeBadges([$_menu_item->getCanonicalName() => $_menu_item->getAmountEvents()]);
            $this->mergeInvolvedUsers($_menu_item->getInvolvedUsers());

            foreach ($_menu_item->getChildren() as $_child) {
                $_events = ($withTime ? $_child->getEventsByDateTime() : $_child->getEventsByDate());

                if ($_events) {
                    $this->mergeTimeline($_events);
                }

                $_amount_events = $_child->getAmountEvents();
                $this->mergeBadges([$_menu_item->getCanonicalName() => $_amount_events]);
                $this->mergeBadges([$_child->getCanonicalName() => $_amount_events]);

                $this->mergeInvolvedUsers($_child->getInvolvedUsers());
            }
        }

        if ($withTime) {
            $this->sortRemoveDoublonsTimelineWithTime();
        } else {
            $this->sortRemoveDoublonsTimeline();
        }
    }

    /**
     * Find attached menus
     *
     * @param ITimelineMenuItem $menu_item - the menu to search for
     *
     * @return ITimelineMenuItem[]
     */
    private function findAttachedMenus(ITimelineMenuItem $menu_item)
    {
        $attached = [];

        foreach ($this->menu_items as $_item) {
            if ($_item instanceof ITimelineAttachableMenuItem) {
                if ($_item->attachedTo() === $menu_item) {
                    $attached[] = $_item;
                }
            }
        }

        return $attached;
    }

    /**
     * @param array $timeline
     *
     * @return void
     */
    private function mergeTimeline(array $timeline)
    {
        foreach (array_unique(array_keys($timeline) + array_keys($this->timeline)) as $_year) {
            if (isset($timeline[$_year])) {
                if (!isset($this->timeline[$_year])) {
                    $this->timeline[$_year] = [];
                }
                $this->timeline[$_year] = array_merge_recursive($this->timeline[$_year], $timeline[$_year]);
            }
        }
    }

    /**
     * @param array $amount_events
     *
     * @return void
     */
    private function mergeBadges(array $amount_events)
    {
        foreach ($amount_events as $_event => $_amount) {
            if (!isset($this->badges[$_event])) {
                $this->badges += $amount_events;
            } else {
                $this->badges[$_event] += $_amount;
            }
        }
    }

    /**
     * @param array $getInvolvedUsers
     *
     * @return void
     * @throws Exception
     */
    private function mergeInvolvedUsers(array $getInvolvedUsers)
    {
        foreach (array_unique($getInvolvedUsers) as $_user) {
            if (!$_user instanceof CMediusers) {
                throw new Exception("This is not a mediuser ! (CTimelineCabinet::mergeInvolvedUsers)");
            }

            if (!isset($this->involved_users[$_user->_id])) {
                $_user->loadRefFunction();

                $this->involved_users[$_user->_id] = $_user;
            }
        }
    }

    private function sortRemoveDoublonsTimeline()
    {
        krsort($this->timeline);
        foreach ($this->timeline as &$year) {
            krsort($year);
            foreach ($year as &$month) {
                krsort($month);
                foreach ($month as &$types) {
                    foreach ($types as &$type) {
                        foreach ($type as &$ids) {
                            $temp_double = [];
                            foreach ($ids as $key => &$item) {
                                if (is_array($item)) {
                                    foreach ($item as $_key => &$_item) {
                                        if (isset($_item->_id)) {
                                            if (!isset($temp_double[$_item->_id])) {
                                                // this will add each name to the $unique array the first time it is encountered
                                                $temp_double[$_item->_id] = true;
                                            } else {
                                                // this will remove all subsequent objects with that name attribute
                                                unset($ids[$_key]);
                                            }
                                        }
                                    }
                                } elseif (isset($item->_id)) {
                                    if (!isset($temp_double[$item->_id])) {
                                        // this will add each name to the $unique array the first time it is encountered
                                        $temp_double[$item->_id] = true;
                                    } else {
                                        // this will remove all subsequent objects with that name attribute
                                        unset($ids[$key]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Sorts the timeline by Year/Month/Date
     *
     * @return void
     */
    private function sortRemoveDoublonsTimelineWithTime()
    {
        krsort($this->timeline);
        foreach ($this->timeline as &$year) {
            krsort($year);
            foreach ($year as &$month) {
                krsort($month);
                foreach ($month as &$time) {
                    krsort($time);
                    foreach ($time as &$types) {
                        foreach ($types as &$type) {
                            foreach ($type as &$ids) {
                                $temp_double = [];
                                foreach ($ids as $key => &$item) {
                                    if (is_array($item)) {
                                        foreach ($item as $_key => &$_item) {
                                            if (isset($_item->_id)) {
                                                if (!isset($temp_double[$_item->_id])) {
                                                    // this will add each name to the $unique array the first time it is encountered
                                                    $temp_double[$_item->_id] = true;
                                                } else {
                                                    // this will remove all subsequent objects with that name attribute
                                                    unset($ids[$_key]);
                                                }
                                            }
                                        }
                                    } elseif (isset($item->_id)) {
                                        if (!isset($temp_double[$item->_id])) {
                                            // this will add each name to the $unique array the first time it is encountered
                                            $temp_double[$item->_id] = true;
                                        } else {
                                            // this will remove all subsequent objects with that name attribute
                                            unset($ids[$key]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

