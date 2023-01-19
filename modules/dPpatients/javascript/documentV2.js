/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DocumentV2 = {
  refresh: function (container) {
    var matches = container.className.match(/documentsV2-(\w+)-(\d+) patient-(\d+) praticien-(\d*)/);

    if (!matches) {
      console.warn(printf("'%s' is not a valid document container", container.className));
      return;
    }

    var url = new Url("patients", "ajax_widget_documents");
    url.addParam("object_class", matches[1]);
    url.addParam("object_id", matches[2]);
    url.addParam("patient_id", matches[3]);
    url.addParam("praticien_id", matches[4]);
    url.addParam("_dummyarg_", container.identify());
    url.requestUpdate(container.down("div.count"));
  },

  viewDocs: function (patient_id, object_id, object_class, callback, ondblclick, with_docs, with_files, with_forms) {
    var callback = callback || Prototype.emptyFunction;
    var url = new Url("patients", "vw_all_docs");
    url.addParam("patient_id", patient_id);
    url.addParam("context_guid", object_class + "-" + object_id);
    url.addParam("ondblclick", ondblclick);
    if (!Object.isUndefined(with_docs)) {
      url.addParam("with_docs", with_docs);
    }
    if (!Object.isUndefined(with_files)) {
      url.addParam("with_files", with_files);
    }
    if (!Object.isUndefined(with_forms)) {
      url.addParam("with_forms", with_forms);
    }
    url.requestModal("80%", "80%", {
      onClose: function () {
        var selector = printf("div.documentsV2-%s-%s", object_class, object_id);
        $$(selector).each(function (elt) {
          DocumentV2.refresh(elt);
        });
        callback();
      }
    });
  },

  addDocument: function (context_guid, patient_id, callback) {
    var callback = callback || Prototype.emptyFunction;
    var url = new Url("patients", "ajax_add_doc");
    var form = getForm("filterDisplay");
    if (form) {
      url.addFormData(form);
    }
    url.addParam("patient_id", patient_id);
    url.addParam("context_guid", context_guid);
    url.requestModal("70%", "70%", {
      onClose: function () {
        callback();
        if (window.TdbTamm) {
          TdBTamm.refreshTimeline(patient_id);
        }
      }
    });
  },

  /**
   * Copy selected documents
   *
   * @param context_guid_copy
   */
  copyDocItems: (context_guid_copy, docitem_guids, prefix = "") => {
    if (!docitem_guids) {
      docitem_guids = $$('div.file-selected').collect((_docitem) => { return _docitem.dataset.docitemGuid; });
    }

    new Url('files', 'copySelectedDocItems', 'dosql')
      .addParam('context_guid_copy', context_guid_copy)
      .addParam('docitem_guids', Object.toJSON(docitem_guids))
      .addParam('prefix', prefix)
      .requestUpdate('systemMsg', {method: 'post', onComplete: Control.Modal.close});
  },

  /**
   * Updates the document list display and saves the preference
   *
   * @param view
   */
  changeView: (view) => {
    App.savePref("display_all_docs", view);
  },

  /**
   * Display or not all the patient's forms and saves the preference
   *
   * @param button
   * @param callback
   */
  toogleAllForms: (button, callback) => {
    if (parseInt($V("display_all_forms"))) {
      button.addClassName("me-dark");
      button.innerHTML = "Afficher tous les formulaires";

      $V("display_all_forms", 0);
      App.savePref("vue_globale_display_all_forms", 0, callback);
    } else {
      button.removeClassName("me-dark");
      button.innerHTML = "Masquer les formulaires supplémentaires";

      $V("display_all_forms", 1);
      App.savePref("vue_globale_display_all_forms", 1, callback);
    }
  }
};
