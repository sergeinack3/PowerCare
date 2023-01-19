/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CheckListGroup = {
  duplicate: function () {
    new Url('salleOp', 'viewDailyCheckListGroup')
      .addParam('check_list_group_id', 0)
      .addParam('duplicate'          , 1)
      .requestModal(0, 0, {
        onClose: Control.Tabs.GroupedTabs.refresh
      });
  },
  
  edit: function (check_list_group_id) {
    new Url('salleOp', 'viewDailyCheckListGroup')
      .addParam('check_list_group_id', check_list_group_id)
      .requestModal('800', null, {
        onClose: Control.Tabs.GroupedTabs.refresh
      });
  },
  
  editChecklist: function (check_list_type_id, check_list_group_id) {
    var url = new Url('salleOp', 'ajax_edit_checklist_type')
      .addParam('check_list_type_id', check_list_type_id)
      .addParam('modal', 1)
      .addParam('callback', 1);
    if (!Object.isUndefined(check_list_group_id)) {
      url.addParam('check_list_group_id', check_list_group_id);
    }
    url.requestModal(500, null, {onClose: Control.Modal.refresh});
  }
};
