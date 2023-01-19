/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Antecedent = {
  remove: function (oForm, onComplete) {
    var oOptions = {
      typeName: 'cet antécédent',
      ajax:     1,
      target:   'systemMsg'
    };

    onComplete = onComplete || Prototype.emptyFunction;
    var oOptionsAjax = {
      onComplete: function () {
        if (window.reloadAtcd) {
          reloadAtcd();
        }
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget("allergie", "allergie_dossier", "list_allergies");
          TdBTamm.refreshDistinctsWidget("atcd", "atcd_dossier", "list_atcd");
        }
        onComplete();
      }
    };

    confirmDeletion(oForm, oOptions, oOptionsAjax);
  },

  cancel: function (oForm, onComplete) {
    $V(oForm.annule, 1);
    onSubmitFormAjax(oForm, function () {
      if (window.reloadAtcdMajeur) {
        reloadAtcdMajeur();
      }
      if (window.reloadAtcd) {
        reloadAtcd();
      }
      if (window.reloadAtcdOp) {
        reloadAtcdOp();
      }
      if (window.refreshWidget) {
        TdBTamm.refreshDistinctsWidget("allergie", "allergie_dossier", "list_allergies");
        TdBTamm.refreshDistinctsWidget("atcd", "atcd_dossier", "list_atcd");
      }
      if (onComplete) {
        onComplete();
      }
    });
    $V(oForm.annule, '');
  },

  restore: function (oForm, onComplete) {
    $V(oForm.annule, '0');
    onSubmitFormAjax(oForm, function () {
      if (window.reloadAtcd) {
        reloadAtcd();
      }
      if (onComplete) {
        onComplete();
      }
    });
    $V(oForm.annule, '');
  },

  toggleCancelled: function (list) {
    $(list).select('.cancelled').invoke('toggle');
  },

  editAntecedents: function (patient_id, type, callback, antecedent_id) {
    var url = new Url("dPpatients", "ajax_edit_antecedents");
    url.addParam("patient_id", patient_id);
    url.addParam("type", type);
    if (antecedent_id) {
      url.addParam("antecedent_id", antecedent_id);
    }
    if (callback) {
      url.addParam('callback', callback);
    }

    url.requestModal(700, 400, {
      onClose: function () {
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget("allergie", "allergie_dossier", "list_allergies");
          TdBTamm.refreshDistinctsWidget("atcd", "atcd_dossier", "list_atcd");
        }
      }
    });
  },

  closeTooltip: function (object_guid) {
    var tooltip = $(object_guid + '_tooltip');
    if (tooltip) {
      tooltip.remove();
    }
  },

  duplicate: function (form) {
    $V(form.dosql, 'do_duplicate_antecedent_aed');
    onSubmitFormAjax(form, {
      onComplete: function () {
        if (onComplete) {
          $V(form.dosql, 'do_antecedent_aed');
        }
      }
    });
  },

  loadAntecedents: function (patient_id, show_atcd, sejour_id, unique_atcd) {
    new Url('patients', 'vw_antecedents_allergies')
      .addParam('patient_id', patient_id)
      .addParam('show_atcd', show_atcd)
      .addParam('sejour_id', sejour_id)
      .requestUpdate('id_antecedents_allergies_' + patient_id + '_' + unique_atcd);
  },

  checkSignificativeElements: function (antecedent_id, sejour_id) {
    var url = new Url('patients', 'ajax_check_antecedents_sejour');
    url.addParam('antecedent_id', antecedent_id);

    if (sejour_id) {
      url.addParam('sejour_id', sejour_id);
    }

    url.requestModal();
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
  },
  /**
   * Cancel all antecedents
   *
   * @param type
   */
  cancelAllAntecedents: function (type) {
    if (confirm($T('CAntecedent-Are you sure you want to cancel all antecedents' + (type ? type : '')))) {
      var antecedent_ids = [];

      $$('.antecedent_element' + (type ? type : '')).each(function (element) {
        antecedent_ids.push(element.get('antecedent_id'));
      });

      new Url('patients', 'do_antecedent_multi_aed', 'dosql')
        .addParam('antecedent_ids[]', antecedent_ids, true)
        .requestUpdate('systemMsg', {
          method: 'post', onComplete: function () {
            DossierMedical.reloadDossierPatient();
          }
        });
    }
  },
  verifyType: function (form, tr_id) {
    if ($V(form.type) === "fam") {
      $(tr_id).show();
      $V(form.family_link,"membre_famille");
    } else {
      $(tr_id).hide();
      $V(form.family_link, "");
    }
  },
};
