/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TypeAnesth = {
  refreshList: function(showInactive) {
    var url = new Url('planningOp', 'vw_edit_typeanesth');
    if (showInactive !== false) {
      url.addParam('inactive', showInactive);
    }
    url.addParam('refresh_mode', 1)
      .requestUpdate('type_anesth_container');
  },

  openModalTypeAnesth : function(type_id) {
    new Url('planningOp', 'ajax_form_typeanesth')
      .addParam('type_anesth',type_id)
      .requestModal();
  },

  submitSaveForm: function(form) {
    return onSubmitFormAjax(form, function() {
      Control.Modal.close();
      this.refreshList(false);
    }.bind(this));
  },

  submitRemoveForm: function(form, objName) {
    confirmDeletion(
      form,
      {objName: objName, ajax: true},
      function() {
        Control.Modal.close();
        this.refreshList(false);
      }.bind(this));
  }
};
