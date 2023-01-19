/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Personnel = {
  refreshList: function (personnel_id) {
    var form = getForm("filterFrm");
    $V(form.personnel_id, personnel_id);
    return onSubmitFormAjax(form, null, "area_personnel");
  },

  edit: function (personnel_id) {
    new Url("personnel", "ajax_edit_personnel")
      .addParam("personnel_id", personnel_id)
      .requestModal("60%", "60%")
  },

  editMultiple: function () {
    new Url("personnel", "ajax_edit_personnel")
      .addParam("multiple", 1)
      .requestModal("60%", "80%");
  },

  store: function (form) {
    return onSubmitFormAjax(form);
  },

  storeMultiple: function (form) {
    var user_ids = [];
    $$('tbody#selected_users input[type="checkbox"]:checked').each(function (element) {
      user_ids.push(element.get('user_id'));
    });
    if (!$V(form.elements["emplacement[]"]).length) {
      alert($T("CPersonnel-msg-Choose a type or types you wish to assign to users selected"));
      return
    }
    var url = new Url('personnel', 'do_multiple_personnel_aed', 'dosql');
    url.addParam('user_id[]', user_ids, true);
    url.addParam('emplacement[]', $V(form.elements['emplacement[]']), true);
    url.addParam('actif', $V(form.elements['actif']));
    url.requestUpdate('systemMsg', {
      method: 'post',
      getParameters: {m: 'personnel', dosql: 'do_multiple_personnel_aed'},
      onComplete: Personnel.afterStore.curry()
    });
  },

  askDelete: function (form, view) {
    confirmDeletion(form, {typeName: 'le personnel ', objName: view, ajax: 1});
  },

  afterStore: function (personnel_id) {
    Control.Modal.close();
    Personnel.refreshList(personnel_id);
  }
};
