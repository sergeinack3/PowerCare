<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Timeline;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Timeline\Timeline;
use Ox\Mediboard\System\Timeline\TimelineMenuItem;
use Ox\Mediboard\System\Timeline\ITimelineCategory;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CTimelineCabinetTest
 */
class CTimelineCabinetTest extends OxUnitTestCase {

  /**
   * Tests a timeline is built and sorted by date
   * @throws Exception
   */
  public function testBuildTimelineTest() {
    $timeline_cat1 = [
      "2019" => [
        "2019-10" => [
          "2019-10-23" => [
            "object" => [new CStoredObject()]
          ]
        ],
        "2019-11" => [
          "2019-11-25" => ["object" => [new CStoredObject(), new CStoredObject()]],
          "2019-11-30" => ["object" => [new CStoredObject()]]
        ],
        "2019-12" => ["2019-12-23" => ["object" => [new CStoredObject(), new CStoredObject()]]],
      ]
    ];

    $menu1 = $this->dataMenu();

    $timeline_cabinet = new Timeline([$menu1]);
    $timeline_cabinet->buildTimeline();
    $this->assertEquals($timeline_cat1, $timeline_cabinet->getTimeline());
  }

  /**
   * Returns a menu with 2 submenus. The main menu has events, the first submenu also has events whereas the 2nd submenu doesn't
   * @return TimelineMenuItem
   */
  public function dataMenu() {
    $m1 = new TimelineMenuItem();
    $m1->setCanonicalName("menu-1");
    $m1->setLogo("class-name");
    $m1->setName("Menu 1");
    $m1->setSelected(true);

    $m11 = new TimelineMenuItem();
    $m11->setCanonicalName("menu-11");
    $m11->setLogo("class-name");
    $m11->setName("Menu 11");
    $m11->setSelected(true);

    $m12 = new TimelineMenuItem();
    $m12->setCanonicalName("menu-12");
    $m12->setLogo("class-name");
    $m12->setName("Menu 12");
    $m12->setSelected(true);

    $m1->setChildren($m11, $m12);

    // Mock two categories
    $cat1 = $this->createMock(ITimelineCategory::class);
    $cat1->method("getInvolvedUsers")->willReturn([]);
    $cat1->method("getEventsByDate")->willReturn(["2019" => ["2019-12" => ["2019-12-23" => ["object" => [new CStoredObject()]]]]]);
    $m1->setTimelineCategory($cat1);

    $cat11 = $this->createMock(ITimelineCategory::class);
    $cat11->method("getInvolvedUsers")->willReturn([]);
    $cat11->method("getEventsByDate")->willReturn(
      [
        "2019" => [
          "2019-10" => ["2019-10-23" => ["object" => [new CStoredObject()]]],
          "2019-12" => ["2019-12-23" => ["object" => [new CStoredObject()]]], // Check if it sorts (cf. months)
          "2019-11" => [
            "2019-11-25" => ["object" => [new CStoredObject(), new CStoredObject()]],
            "2019-11-30" => ["object" => [new CStoredObject()]]
          ]
        ]
      ]
    );
    $m11->setTimelineCategory($cat11);

    // Lets start testing ...
    return $m1;
  }
}
