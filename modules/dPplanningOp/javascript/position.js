/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Position = {
  edit: function(position_id) {
    var url = new Url('planningOp', 'vw_edit_position');
    url.addParam('position_id', position_id);
    url.requestModal();
  },
  refreshList: function(show_inactive) {
    var url = new Url('planningOp', 'vw_positions');
    if (!Object.isUndefined(show_inactive)) {
      url.addParam('show_inactive', show_inactive);
    }
    url.addParam('refresh', '1');
    url.requestUpdate("list_positions");
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
        Position.refreshList();
      }}
    );
  },
  confirmDeletion: function(form) {
    var options = {
      objName: $V(form.nom),
      ajax: 1
    };
    var ajax = {
      onComplete: function() {
        Control.Modal.close();
        Position.refreshList();
      }
    };
    confirmDeletion(form, options, ajax);
  }
}