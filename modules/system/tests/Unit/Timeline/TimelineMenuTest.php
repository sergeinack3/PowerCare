<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Timeline;

use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Mediboard\System\Timeline\MenuTimelineCategory;
use Ox\Mediboard\System\Timeline\TimelineMenu;
use Ox\Mediboard\System\Timeline\TimelineMenuItem;
use Ox\Tests\OxUnitTestCase;

/**
 * Class TimelineMenuTest which tests TimelineMenuItem
 */
class TimelineMenuTest extends OxUnitTestCase {
  /**
   * Get data without a category must return an empty array
   */
  public function testGetInvolvedUsersWithoutCategory() {
    $item1 = new TimelineMenuItem();

    $this->assertEquals([], $item1->getInvolvedUsers());
  }

  /**
   * By default, when a menu 'select state' has not been set it must return an empty
   */
  public function testGetInvolvedUsersWithSelectedNotSet() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getInvolvedUsers")->willReturn([new CMediusers(), new CMediusers()]);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);

    $this->assertEquals([], $item1->getInvolvedUsers());
  }

  /**
   * When the menu is specifically set to false it must return an empty array
   */
  public function testGetInvolvedUsersWithSelectedSetToFalse() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getInvolvedUsers")->willReturn([new CMediusers(), new CMediusers()]);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);
    $item1->setSelected(false);

    $this->assertEquals([], $item1->getInvolvedUsers());
  }

  /**
   * When all has been well set, it should return an array with users
   */
  public function testGetInvolvedUsersWithSelectedSetToTrue() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getInvolvedUsers")->willReturn([new CMediusers(), new CMediusers()]);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);
    $item1->setSelected(true);

    $this->assertEquals([new CMediusers(), new CMediusers()], $item1->getInvolvedUsers());
  }

  /**
   * Get data without a category must return an empty array
   */
  public function testGetEventsByDateWithoutCategory() {
    $item1 = new TimelineMenuItem();

    $this->assertEquals([], $item1->getEventsByDate());
  }

  /**
   * By default, when a menu 'select state' has not been set it must return an empty
   */
  public function testGetEventsByDateWithSelectedNotSet() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getEventsByDate")->willReturn(['bla', 'bla', 'bla']);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);

    $this->assertEquals([], $item1->getEventsByDate());
  }

  /**
   * When the menu is specifically set to false it must return an empty array
   */
  public function testGetEventsByDateWithSelectedSetToFalse() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getEventsByDate")->willReturn(['bla', 'bla', 'bla']);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);
    $item1->setSelected(false);

    $this->assertEquals([], $item1->getInvolvedUsers());
  }

  /**
   * When all has been well set, it should return an array with data
   */
  public function testGetEventsByDateWithSelectedSetToTrue() {
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getEventsByDate")->willReturn(['bla', 'bla', 'bla']);

    $item1 = new TimelineMenuItem();
    $item1->setTimelineCategory($cat1);
    $item1->setSelected(true);

    $this->assertEquals(['bla', 'bla', 'bla'], $item1->getEventsByDate());
  }

  /**
   * The category color value function must return a string to be used in the frontend
   */
  public function testGetCategoryColor() {
    $item1 = new TimelineMenuItem();
    $item1->setCategoryColor(MenuTimelineCategory::NONE());

    $this->assertEquals('none', $item1->getCategoryColorValue());
  }
}
