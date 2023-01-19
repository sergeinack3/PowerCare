/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

LaboBacterio = {
  edit: function (laboratoire_bacterio_id) {
    new Url('planningOp', 'ajax_edit_laboratoire_bacterio')
      .addParam('laboratoire_bacterio_id', laboratoire_bacterio_id)
      .requestModal('600', '600', {onClose: (this.refreshList).bind(this)});
  },

  refreshList: function() {
    new Url('planningOp', 'ajax_list_laboratoires_bacterio')
      .requestUpdate('labos_bacterio_area');
  },

  submit: function(form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  merge: function() {
    var labo_ids = $$('input.merge_labo_bacterio:checked');

    if (labo_ids.length > 2) {
      return alert($T('CLaboratoire-Can merge only 2 laboratories'));
    }

    new Url('system', 'object_merger')
      .addParam('objects_class', 'CLaboratoireBacterio')
      .addParam('objects_id', labo_ids.pluck('value').join('-'))
      .popup(800, 600, 'merge_labos');
  },
};
