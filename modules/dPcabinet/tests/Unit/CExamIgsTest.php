<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CExamIgsTest
 *
 * @package Ox\Mediboard\Cabinet\Tests\Unit
 */
class CExamIgsTest extends OxUnitTestCase {

  /**
   * @return array
   */
  private function scoresDepends() {
    $scores_object = array();

    // Make and add scores from the last 24 hours
    $score_igs           = new CExamIgs();
    $score_igs->scoreIGS = 23;
    $score_igs->date     = CMbDT::dateTime("-5 hours");
    $scores_object[]     = $score_igs;

    $score_igs           = new CExamIgs();
    $score_igs->scoreIGS = 29;
    $score_igs->date     = CMbDT::dateTime("-18 hours");
    $scores_object[]     = $score_igs;

    $score_igs           = new CExamIgs();
    $score_igs->scoreIGS = 8;
    $score_igs->date     = CMbDT::dateTime("-2 hours");
    $scores_object[]     = $score_igs;

    return $scores_object;
  }

  /**
   * Gets the IGS score using a set of IGS score objects
   * Reminder: the IGS score is the worse (highest) value over the last 24 hours
   *
   * @throws CMbException
   */
  public function testGetIGSFromList() {
    $scores_object = self::scoresDepends();
    $actual = CExamIgs::getIGSFromList($scores_object);

    static::assertEquals(29, $actual);

    // Adding a worse IGS score but shouldn't return it (over 24 hours)
    $score_igs           = new CExamIgs();
    $score_igs->scoreIGS = 37;
    $score_igs->date     = CMbDT::date("-48 hours");
    $scores_object[]     = $score_igs;

    $actual = CExamIgs::getIGSFromList($scores_object);

    static::assertEquals(29, $actual);
  }

  /**
   * Tests the sorting date in an object array
   *
   * @throws CMbException
   */
  public function testSortObjectDate() {
    $scores_object = self::scoresDepends();
    $expected_sorted = array($scores_object[1], $scores_object[0], $scores_object[2]);

    $sorted_objects = CExamIgs::sortObjectDate($scores_object, "date");

    static::assertEquals($expected_sorted, $sorted_objects);
  }
}
