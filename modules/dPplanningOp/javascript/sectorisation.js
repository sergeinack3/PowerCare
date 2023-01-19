/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Sectorisation = {
  edit : function(id_rule) {
    this.editRequest(id_rule, 0);
  },
  clone : function(id_rule) {
    this.editRequest(id_rule, 1);
  },
  editRequest: function(id_rule, clone) {
    new Url("planningOp", "ajax_edit_rule_sectorisation")
      .addParam("rule_id", id_rule)
      .addParam("clone", clone)
      .requestModal();
  },

  changePrio: function(add) {
    var form = getForm('editRegleSectorisation');
    $V(form.priority, isNaN(parseInt($V(form.priority))) ? '0' : Math.max(0, parseInt($V(form.priority)) + add));
  },

  submitSaveForm: function(form) {
    return onSubmitFormAjax(
      form,
      function() {
        Control.Modal.close();
        this.refreshList(false);
      }.bind(this));
  },

  submitRemoveForm: function(form, objName) {
    return confirmDeletion(
      form,
      {objName:objName},
      function() {
        Control.Modal.close();
        this.refreshList(false);
      }.bind(this));
  },

  refreshList: function(showInactive) {
    var url = new Url('planningOp', 'vw_sectorisations');
    if (showInactive !== false) {
      url.addParam('inactive', showInactive);
    }
    url.addParam('refresh_mode', 1)
      .requestUpdate('sectorisation_container');
  },
};
