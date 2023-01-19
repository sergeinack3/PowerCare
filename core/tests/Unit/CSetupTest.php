<?php

/**
 * @package Mediboard\\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CSetup;
use Ox\Tests\OxUnitTestCase;

class CSetupTest extends OxUnitTestCase {

  /**
   * @return CSetup
   */
  public function testConstruct() {
    $setup = new CSetup();
    $this->assertInstanceOf(CSetup::class, $setup);

    return $setup;
  }

  /**
   * @param CSetup $setup
   */
  public function testMakeRevision() {
    $setup = new CSetup();
    $setup->makeRevision('0.1');
    $setup->makeRevision('0.123');
    $this->assertEquals('0.1', $setup->revisions[0]);
    $this->assertEquals('0.123', $setup->revisions[1]);
  }

  /**
   *
   */
  public function testMakeRevisionFaild() {
    $setup = new CSetup();
    $setup->makeRevision('0.1');
    $this->expectError();
    $setup->makeRevision('0.1');
  }

  /**
   *
   */
  public function testMakeEmptyRevision() {
    $setup = new CSetup();
    $setup->makeEmptyRevision('0.1');
    $this->assertNotEmpty($setup->queries);
    $this->assertEquals(end($setup->queries['0.1']), ['SELECT 0', false, null]);
  }

  /**
   *
   */
  public function testAddQuery() {
    $setup = new CSetup();
    $setup->makeRevision('0.1');
    $query = "CREATE TABLE `toto_tata` (
                `toto_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `tata_id` VARCHAR (80)  NOT NULL
              )/*! ENGINE=MyISAM */;";
    $setup->addQuery($query);

    $query2 = "SELECT * FROM `toto_tata`;";
    $setup->addQuery($query2);

    $this->assertCount(2, $setup->queries['0.1']);
    $this->assertEquals($setup->queries['0.1'][0], [$query, false, null]);
    $this->assertEquals($setup->queries['0.1'][1], [$query2, false, null]);
  }

  /**
   *
   */
  public function testAddDependency() {
    $setup = new CSetup();
    $setup->makeRevision('0.1');
    $setup->addDependency('system', '0.1.2');
    $object = $setup->dependencies['0.1'][0];
    $this->assertInstanceOf(\stdClass::class, $object);
    $this->assertEquals($object->module, 'system');
    $this->assertEquals($object->revision, '0.1.2');
  }
}
