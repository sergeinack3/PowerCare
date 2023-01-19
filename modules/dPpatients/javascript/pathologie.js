/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Pathologie = {
  remove: function (form) {
    var oOptions = {
      typeName: 'cette pathologie',
      target:   'systemMsg'
    };

    var oOptionsAjax = {
      onComplete: function () {
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
        }
      }
    };

    confirmDeletion(form, oOptions, oOptionsAjax);
  },

  edit: function (pathologie_id) {
    new Url("patients", "ajax_edit_pathologie")
      .addParam("pathologie_id", pathologie_id)
      .requestModal(700, 400, {
        onClose: function () {
          if (window.refreshWidget) {
            TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
          }
        }
      });
  },

  cancel: function (form) {
    $V(form.annule, 1);
    $V(form.resolu, 0);

    onSubmitFormAjax(form, function () {
      if (window.refreshWidget) {
        TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
      }
    });
  },

  restore: function (form) {
    $V(form.annule, 0);
    $V(form.resolu, 0);

    onSubmitFormAjax(form, function () {
      if (window.refreshWidget) {
        TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
      }
    });
  },

  duplicate: function (form) {
    $V(form.dosql, "do_duplicate_pathologie");
    onSubmitFormAjax(form);
  },

  addAtcd: function (form) {
    var url = new Url("patients", "vw_add_pathologie_to_atcd");
    url.addFormData(form);
    url.requestModal();
  },

  /**
   * Resolve a health issue
   *
   * @param form
   */
  resolve: function (form) {
    $V(form.resolu, 1);
    $V(form.annule, 0);

    onSubmitFormAjax(form, function () {
      if (window.refreshWidget) {
        TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
      }
    });
  },

  /**
   * Unresolve a health issue
   *
   * @param form
   */
  unresolve: function (form) {
    $V(form.resolu, 0);
    $V(form.annule, 0);

    onSubmitFormAjax(form, function () {
      if (window.refreshWidget) {
        TdBTamm.refreshDistinctsWidget("pathologie", "pathologie_dossier", "list_pathologies");
      }
    });
  },

  /**
   * Show different nomenclatures (eg: Loinc, Snomed,...)
   *
   * @param object_guid
   */
  showNomenclatures: function (object_guid) {
    new Url('patients', 'ajax_vw_nomenclatures')
      .addParam('object_guid', object_guid)
      .requestModal('60%', '80%', {onClose: Control.Modal.refresh});
  }
};