<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;
use Ox\Tests\OxUnitTestCase;

class CPlanningWeekTest extends OxUnitTestCase {

  /**
   * @return CPlanningWeek
   */
  private function createPlanning(): CPlanningWeek {
    return new CPlanningWeek(
      CMbDT::date('monday this week', CMbDT::date()),
      CMbDT::date('monday this week', CMbDT::date()),
      CMbDT::date('sunday this week', CMbDT::date()),
      7,
      false
    );
  }

  /**
   * Test filling rate update with one free event
   */
  public function testUpdateDaysCompletionAddFree() {
    $planning = $this->createPlanning();
    $event = new CPlanningEvent("", CMbDT::date());
    $event->type = "rdvfree";

    $planning->addEvent($event);

    $expected = [
      CMbDT::date() => [
        "total" => 1,
        "full" => 0
      ]
    ];
    $this->assertEquals($expected, $planning->getCompletion());
  }

  /**
   * Test filling rate update with one full event
   */
  public function testUpdateDaysCompletionAddFull() {
    $planning = $this->createPlanning();
    $event = new CPlanningEvent("", CMbDT::date());
    $event->type = "rdvfull";

    $planning->addEvent($event);

    $expected = [
      CMbDT::date() => [
        "total" => 1,
        "full" => 1
      ]
    ];
    $this->assertEquals($expected, $planning->getCompletion());
  }

  /**
   * Test filling rate update with one free event and one full event
   */
  public function testUpdateDaysCompletionAddFreeAndFull() {
    $planning = $this->createPlanning();
    $event_free = new CPlanningEvent("", CMbDT::date());
    $event_free->type = "rdvfree_tamm";
    $event_full = new CPlanningEvent("", CMbDT::date());
    $event_full->type = "rdvfull";

    $planning->addEvent($event_free);
    $planning->addEvent($event_full);

    $expected = [
      CMbDT::date() => [
        "total" => 2,
        "full" => 1
      ]
    ];
    $this->assertEquals($expected, $planning->getCompletion());
  }
}
