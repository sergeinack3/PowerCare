/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Idex = {
  edit: function (object_guid, tag) {
    var parts = object_guid.split("-");

    new Url('sante400', 'ajax_edit_identifiant')
      .addParam("object_class", parts[0])
      .addParam("object_id", parts[1])
      .addParam('tag', tag)
      .addParam('load_unique', 1)
      .addParam('dialog', 1)
      .requestModal(400);
  },

  edit_manually: function (sejour_guid, patient_guid, callback) {
    new Url("dPsante400", "ajax_edit_manually_ipp_nda")
      .addParam("sejour_guid", sejour_guid)
      .addParam("patient_guid", patient_guid)
      .requestModal("40%", "40%")
      .modalObject.observe("afterClose", callback)
  },

  submit_ipp_nda: function (name_form_nda, name_form_ipp) {
    var form_nda = getForm("edit" + name_form_nda);
    var form_ipp = getForm("edit" + name_form_ipp);

    if (form_ipp.id400 && !checkForm(form_ipp) || form_nda.id400 && !checkForm(form_nda)) {
      return false;
    }

    if (form_nda.id400) {
      form_nda.onsubmit();
    }

    if (form_ipp.id400) {
      form_ipp.onsubmit();
    }

    Control.Modal.close();
  },

  list_identifiants: function () {
    const object_class = document.getElementById('object_class').value;
    const object_id = document.getElementById('object_id').value;
    const id400 = document.getElementById('id400').firstElementChild.value;
    const tag = document.getElementById('tag').firstElementChild.value;

    const url = new Url('dPsante400', 'listIdentifiants');
    url.addParam("object_class", object_class);
    url.addParam("object_id", object_id);
    url.addParam("id400", id400);
    url.addParam("tag", tag);
    url.requestUpdate('list_identifiants');
  },

  find_duplicated: function () {
    const object_class = document.getElementById('object_class').value;
    const object_id = document.getElementById('object_id').value;
    const id400 = document.getElementById('id400').firstElementChild.value;
    const tag = document.getElementById('tag').firstElementChild.value;

    const url = new Url('dPsante400', 'listDuplicated');
    url.addParam("object_class", object_class);
    url.addParam("object_id", object_id);
    url.addParam("id400", id400);
    url.addParam("tag", tag);
    url.addParam("looking_for_duplicated", true);
    url.requestUpdate('list_identifiants');
  }
};
