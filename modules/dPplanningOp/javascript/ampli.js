/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Ampli = {
  edit: function (ampli_id) {
    new Url('planningOp', 'ajax_edit_ampli')
      .addParam('ampli_id', ampli_id)
      .requestModal('600', '600', {onClose: (this.refreshList).bind(this)});
  },

  refreshList: function() {
    new Url('planningOp', 'ajax_list_amplis')
      .requestUpdate('amplis_area');
  },

  submit: function(form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  merge: function() {
    var amplis_ids = $$('input.merge_ampli:checked');

    if (amplis_ids.length > 2) {
      return alert($T('CAmpli-Can merge only 2 amplis'));
    }

    new Url('system', 'object_merger')
      .addParam('objects_class', 'CAmpli')
      .addParam('objects_id', amplis_ids.pluck('value').join('-'))
      .popup(800, 600, 'merge_amplis');
  },
};
