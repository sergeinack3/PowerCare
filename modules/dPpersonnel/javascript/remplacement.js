/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Remplacement = {
  refreshList: function (user_id, hide_old) {
    var url = new Url('personnel', 'vw_remplacement_user');
    url.addParam('user_id', user_id);
    if (!Object.isUndefined(hide_old)) {
      url.addParam('hide_old', hide_old);
    }
    url.requestUpdate('remplacements-user_id');
    PlageConge.content("remplacement");
  },
  edit: function (remplacement_id, user_id) {
    var url = new Url("personnel", "vw_edit_remplacement");
    url.addParam("remplacement_id", remplacement_id);
    url.addParam('user_id', user_id);
    url.requestModal();
  },
  onSubmit: function (form) {
    if ($V(form.user_id) != $V(form.remplace_id) && $V(form.user_id) != $V(form.remplacant_id)) {
      alert($T('CRemplacement.no_user_current'));
      return false;
    }
    return onSubmitFormAjax(form);
  },
  askDelete: function (form) {
    confirmDeletion(form, {typeName: 'le remplacement ', objName: $V(form.libelle), ajax: 1});
  },
  afterStore: function () {
    Control.Modal.close();
    Remplacement.refreshList();
  }
};