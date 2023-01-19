<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFilesCategoryDispatcher;
use Ox\Mediboard\Populate\Generators\CFileGenerator;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CFilesCategoryDispatcherTest extends OxUnitTestCase
{
    public function testGetStatsNoData(): void
    {
        $cat = new CFilesCategory();

        $mock = $this->getMockBuilder(CFilesCategoryDispatcher::class)
            ->setConstructorArgs([$cat])
            ->onlyMethods(['getDistinctGroupsUsers'])
            ->getMock();
        $mock->method('getDistinctGroupsUsers')->willReturn([]);

        $this->assertEquals([], $mock->getStats());
    }
}
