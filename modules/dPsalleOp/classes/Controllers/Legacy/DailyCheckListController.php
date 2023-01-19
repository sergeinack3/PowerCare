<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CDailyCheckListGroup;
use Ox\Mediboard\SalleOp\Repository\DailyCheckListGroupRepository;

/**
 * Legacy Controller for the daily check list or check list item
 */
class DailyCheckListController extends CLegacyController
{
    /**
     * View the daily check list group
     *
     * @return void
     * @throws Exception
     */
    public function viewDailyCheckListGroup(): void
    {
        $this->checkPermAdmin();

        $check_list_group_id = CView::get('check_list_group_id', 'ref class|CDailyCheckListGroup');
        $duplicate           = CView::get('duplicate', 'bool default|0');

        CView::checkin();

        $check_list_group = CDailyCheckListGroup::findOrNew($check_list_group_id);
        $daily_check_list_group_repository = new DailyCheckListGroupRepository();
        $check_list_groups = $daily_check_list_group_repository->findAllWithChecklistAndCategories(
            $check_list_group
        );

        $view_name = "vw_daily_check_list_group";

        if ($check_list_group_id !== null) {
            $view_name = "inc_edit_check_list_group";
        }

        $this->renderSmarty(
            $view_name,
            [
                "check_list_groups" => $check_list_groups,
                "check_list_group"  => $check_list_group,
                "duplicate"         => $duplicate,
            ]
        );
    }
}
