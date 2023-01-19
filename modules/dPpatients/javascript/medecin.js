/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

Medecin = {
  form:      null,
  sFormName: "editSejour",

  edit:      function (form, nom, function_id, medecin_type, view_update) {
    this.form = form;
    medecin_type = medecin_type || "";
    var url = new Url("patients", "vw_correspondants");
    url.addParam("dialog", "1");
    url.addParam("medecin_type", medecin_type);
    url.addParam("view_update", view_update);
    url.addParam("medecin_nom", nom);
    url.addParam("medecin_function_id", function_id);
    url.requestModal("1000", "100%");
  },

  set: function (id, view) {
    $('_adresse_par_prat').show().update('Autres : <span>' + view + '</span>');
    $V(this.form.adresse_par_prat_id, id);
    $V(this.form._correspondants_medicaux, '', false);
  },

  del:          function (form) {
    if (!$V(form.medecin_traitant) || confirm("Voulez vous vraiment supprimer ce médecin du dossier patient ?")) {
      if (form._view) {
        $V(form._view, '');
      }
      if (form.medecin_traitant) {
        if ($V(form.medecin_traitant)) {
          Control.Tabs.setTabCount("medecins", "-1");
          $V(form.medecin_traitant, '');
        }
      } else {
        Control.Tabs.setTabCount("medecins", "-1");
        $V(form.del, 1);
      }
    }
  },
  delPharmacie: function (form) {
    if (confirm("Voulez vous vraiment supprimer cette pharmacie du dossier patient ?")) {
      if (form._view) {
        $V(form._view, '');
      }
      if (form.pharmacie_id) {
        if ($V(form.pharmacie_id)) {
          Control.Tabs.setTabCount("medecins", "-1");
          $V(form.pharmacie_id, '');
        }
      } else {
        Control.Tabs.setTabCount("medecins", "-1");
        $V(form.del, 1);
      }
    }
  },

  doMerge: function (sform) {
    if (sform) {
      var url = new Url();
      url.setModuleAction("system", "object_merger");
      url.addParam("objects_class", "CMedecin");
      url.addParam("objects_id", $V(getForm(sform)["objects_id[]"]).join("-"));
      url.popup(800, 600, "merge_patients");
    }
  },

  editMedecin: function (medecin_id, callback, medecin_type, compte_rendu_id) {
    let url = new Url('dPpatients', 'editMedecin');
    url.addParam('medecin_id', medecin_id);
    url.addParam('compte_rendu_id', compte_rendu_id);
    if (!Object.isUndefined(medecin_type)) {
      url.addParam('medecin_type', medecin_type);
    }
    url.requestModal('500');
    if (!Object.isUndefined(callback)) {
      url.modalObject.observe('afterClose', function () {
        callback();
      });
    }
  },

  duplicate: function (medecin_id, callback) {
    var url = new Url('dPpatients', 'editMedecin');
    url.addParam('medecin_id', medecin_id);
    url.addParam('duplicate', 1);
    url.requestModal('500');
    if (!Object.isUndefined(callback)) {
      url.modalObject.observe('afterClose', function () {
        callback();
      });
    }
  },

  viewPrint: function (medecin_id) {
    var url = new Url('dPpatients', 'print_medecin');
    url.addParam('medecin_id', medecin_id);
    url.popup(700, 550);
  },

  toggleMedTraitant: function (input) {
    var label = input.up('label');

    if (input.checked) {
      label.previous('button.cancel').click();
    }

    label.previous('button.search').writeAttribute('disabled', input.checked ? 'disabled' : null);
    $('traitant-edit-'+input.dataset.patientId+'__view').writeAttribute('readonly', input.checked ? 'readonly' : null);

    if (input.checked) {
      $V(getForm('editFrm').medecin_traitant_declare, '0');
      $V(input.form.medecin_traitant_declare, '0');
    } else {
      $('editFrm_medecin_traitant_declare_').checked = true;
      $V(input.form.medecin_traitant_declare, '');
    }
  },

  listExercicePlaces: (medecin_id) => {
    new Url('patients', 'listMedecinExercicePlaces')
      .addParam('medecin_id', medecin_id)
      .requestModal('80%');
  },
  reloadCorrespondantMedical:(form) => {
    return onSubmitFormAjax(form, function() { Control.Modal.refresh(); });
  }
};
