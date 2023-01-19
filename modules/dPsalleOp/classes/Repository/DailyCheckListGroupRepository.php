<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp\Repository;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\SalleOp\CDailyCheckListGroup;

/**
 * CDailyCheckList repository
 */
class DailyCheckListGroupRepository implements IShortNameAutoloadable
{
    /**
     * Find all CDailyCheckListGroup object with CDailyCheckListType and their categories
     *
     * @param CDailyCheckListGroup $check_list_group
     *
     * @return array
     * @throws Exception
     */
    public function findAllWithChecklistAndCategories(CDailyCheckListGroup $check_list_group): array
    {
        foreach ($check_list_group->loadRefChecklist() as $list_type) {
            $list_type->loadRefsCategories();
        }

        return $check_list_group->loadGroupList(null, 'actif DESC, title');
    }
}
