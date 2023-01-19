/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Naissance = {
  edit: function (naissance_id, operation_id, sejour_id, provisoire, callback, onclose) {
    new Url('maternite', 'ajax_edit_naissance')
      .addParam('naissance_id', naissance_id)
      .addNotNullParam('operation_id', operation_id)
      .addNotNullParam('sejour_id', sejour_id)
      .addNotNullParam('provisoire', provisoire)
      .addNotNullParam("callback", callback)
      .requestModal("75%", "75%", {
        onClose: function () {
          Naissance.callbackRefresh(operation_id, sejour_id, onclose);
        }
      });
  },

  editSalleNaissance: function (naissance_id, operation_id) {
    new Url('maternite', 'edit_nouveau_ne_salle_naissance')
      .addParam('naissance_id', naissance_id)
      .requestModal("90%", "90%", {onClose: Naissance.reloadNaissances.curry(operation_id)});
  },

  editResumeSejour: function (naissance_id, operation_id) {
    new Url('maternite', 'edit_nouveau_ne_resume_sejour')
      .addParam('naissance_id', naissance_id)
      .requestModal("90%", "90%", {onClose: Naissance.reloadNaissances.curry(operation_id)});
  },

  reloadNaissances: function (operation_id, sejour_id) {
    if (!$('naissance_area')) {
      return;
    }
    new Url('maternite', 'ajax_vw_naissances')
      .addParam('operation_id', operation_id)
      .addParam('sejour_id', sejour_id)
      .requestUpdate('naissance_area');
  },

  /**
   * Refresh the pregnancy
   *
   * @param operation_id
   * @param with_buttons
   */
  refreshGrossesse: function (operation_id, with_buttons) {
    if (!$("grossesse")) {
      return;
    }
    new Url("maternite", "ajax_vw_grossesse")
      .addParam('operation_id', operation_id)
      .addParam('with_buttons', with_buttons)
      .requestUpdate('grossesse');
  },

  confirmDeletion: function (form) {
    var options = {
      typeName: 'la naissance',
      ajax:     1
    };

    confirmDeletion(form, options, Control.Modal.close);
  },

  printDossier: function (sejour_id) {
    var url = new Url("hospi", "httpreq_documents_sejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("only_sejour", 1);
    url.requestModal(700, 400);
  },

  doMerge: function (form) {
    new Url("system", "object_merger")
      .addParam("objects_class", "CNaissance")
      .addParam("objects_id", $V(form["objects_id[]"]).join("-"))
      .popup(800, 600, "merge_naissances");
  },

  callbackRefresh: function (operation_id, sejour_id, onclose) {
    if (operation_id || sejour_id) {
      Naissance.reloadNaissances(operation_id, sejour_id);
    }
    if (window.DossierMater && DossierMater.currentPage && DossierMater.currentPage.modalObject) {
      DossierMater.currentPage.refreshModal();
    }
    if (onclose) {
      onclose();
    }
  },

  cancelNaissance: function (naissance_id, operation_id, sejour_id) {
    if (!confirm($T("CNaissance-confirm_cancel"))) {
      return;
    }

    new Url("maternite", "do_cancel_naissance", "dosql")
      .addParam("naissance_id", naissance_id)
      .requestUpdate("systemMsg", {
        method:        "post",
        getParameters: {
          m:     "maternite",
          dosql: "do_cancel_naissance"
        },
        onComplete:    Naissance.callbackRefresh.curry(operation_id, sejour_id)
      });
  },

  checkNaissance: function (form) {

    if (form.provisoire.value === '1') {
      return onSubmitFormAjax(form, Control.Modal.close);
    } else if (Patient.checkBirthNameMatchesNames(true, form.name)) {
      return onSubmitFormAjax(form, Control.Modal.close);
    } else {
      return false;
    }
  },
};
