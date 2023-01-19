<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Fixtures;

use Ox\Core\CModelObjectException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

/**
 * Fixtures for testing the CFileCategory class
 */
class FilesCategoryFixtures extends Fixtures
{
    public const REF_EMERGENCY_CAT_GLOBAL = "Emergency_Global";

    public const REF_EMERGENCY_CAT_GROUPS = "Emergency_Groups";

    public const REF_FILE_CAT_GROUPS = "File_Cat_Groups";

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function load(): void
    {
        $this->createEmergencyCategories();
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createEmergencyCategories(): void
    {
        $file_cat_global                   = CFilesCategory::getSampleObject();
        $file_cat_global->nom              = "Emergency_Global";
        $file_cat_global->is_emergency_tab = 1;

        $this->store($file_cat_global, self::REF_EMERGENCY_CAT_GLOBAL);

        $group       = CGroups::getSampleObject();
        $group->text = "Lorem";

        $this->store($group, self::REF_FILE_CAT_GROUPS);

        $file_cate_group                   = CFilesCategory::getSampleObject();
        $file_cate_group->nom              = "Emergency_Global";
        $file_cate_group->is_emergency_tab = 1;
        $file_cate_group->group_id         = $group->_id;

        $this->store($file_cate_group, self::REF_EMERGENCY_CAT_GROUPS);
    }
}
