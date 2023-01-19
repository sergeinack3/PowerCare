<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Exception;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\Exceptions\FilesCategoryException;
use Ox\Mediboard\Files\Tests\Fixtures\FilesCategoryFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CFilesCategoryTest extends OxUnitTestCase
{
    /**
     * @throws FilesCategoryException
     * @throws Exception
     */
    public function testGetEmergencyTabsThrowsException(): void
    {
        $this->expectExceptionObject(FilesCategoryException::groupIsNull());

        CFilesCategory::getEmergencyTabCategories(new CGroups());
    }

    /**
     * @param CGroups $group
     * @param array   $expected
     *
     * @return void
     * @throws FilesCategoryException
     * @throws TestsException
     */
    public function testGetEmergencyTabContainsFileCategory(): void
    {
        /** @var CGroups $group */
        $group      = $this->getObjectFromFixturesReference(CGroups::class, FilesCategoryFixtures::REF_FILE_CAT_GROUPS);
        $cat_global = $this->getObjectFromFixturesReference(
            CFilesCategory::class,
            FilesCategoryFixtures::REF_EMERGENCY_CAT_GLOBAL
        );
        $cat_group  = $this->getObjectFromFixturesReference(
            CFilesCategory::class,
            FilesCategoryFixtures::REF_EMERGENCY_CAT_GROUPS
        );

        $actual = CFilesCategory::getEmergencyTabCategories($group);

        $this->assertArrayHasKey($cat_global->_id, $actual);
        $this->assertArrayHasKey($cat_group->_id, $actual);
    }
}
