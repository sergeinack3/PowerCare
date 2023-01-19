/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Correspondant = {

  edit: function (correspondant_id, patient_id, callback, duplicate) {
    var url = new Url('dPpatients', 'ajax_form_correspondant');
    url.addParam('correspondant_id', correspondant_id);
    url.addParam("patient_id", patient_id);
    url.addNotNullParam("duplicate", duplicate);
    url.requestModal("380px", "95%");
    if (!Object.isUndefined(callback)) {
      url.modalObject.observe("afterClose", function () {
        callback();
        if (patient_id) {
          Patient.edit(patient_id)
        }
      });
    }
  },

  duplicate: function (correspondant_id, patient_id, callback) {
    var url = new Url('dPpatients', 'ajax_form_correspondant');
    url.addParam('correspondant_id', correspondant_id);
    url.addParam("patient_id", patient_id);
    url.addParam("duplicate", true);
    url.requestModal(600, "95%");
    if (!Object.isUndefined(callback)) {
      url.modalObject.observe("afterClose", function () {
        callback();
        if (patient_id) {
          Patient.edit(patient_id)
        }
      });
    }
  },

  onSubmit: function (form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        Control.Modal.close();
      }
    });
  },

  confirmDeletion: function (form) {
    var options = {
      typeName: 'correspondant',
      objName:  $V(form.nom),
      ajax:     1
    };

    var ajax = {
      onComplete: function () {
        Control.Modal.close();
      }
    };

    confirmDeletion(form, options, ajax);
  },

  refreshList: function (patient_id) {
    var url = new Url('dPpatients', 'ajax_list_correspondants');
    url.addParam("patient_id", patient_id);
    url.requestUpdate('list-correspondants');

    var form = getForm('editFrm');
    if (form && window.Patient && Patient.refreshInfoTutelle) {
      Patient.refreshInfoTutelle($V(form.tutelle));
    }
  },

  checkCorrespondantMedical: (form, object_class, object_id, use_meff) => {
    if ($V(form.patient_id) != '') {
        new Url('patients', 'checkCorrespondantMedical')
            .addParam('patient_id', $V(form.patient_id))
            .addParam('object_id', object_id)
            .addParam('object_class', object_class)
            .addParam('use_meff', Object.isUndefined(use_meff) ? 1 : use_meff)
            .requestUpdate('correspondant_medical');

        // Au changement de patient, on vide la liste des lieux d'exercice
        $('medecin_exercice_place').update();
    }
  },

  reloadExercicePlaces: (medecin_id, object_class, object_id, field) => {
    // Si pas de médecin, on vide la div qui contient les lieux d'exercices
    if (!medecin_id || medecin_id === '') {
      $('medecin_exercice_place').update();
      return;
    }

    new Url('patients', 'chooseExercicePlace')
      .addParam('medecin_id', medecin_id)
      .addParam('object_class', object_class)
      .addParam('object_id', object_id)
      .addParam('field', field)
      .requestUpdate('medecin_exercice_place');
  },

  /**
   * Ouverture de la modal permettant de correspondants
   */
  openCorrespondantImportFromRPPSModal() {
    new Url('dPpatients', 'openCorrespondantImportFromRPPSModal')
      .requestModal('75%', '75%');
  },

  refreshPageCorrespondant: function (page) {
    var oform = getForm('correspondantFilterForm');
    if (oform) {
      $V(oform.start, page);
      oform.onsubmit();
    }
  },

  /**
   * Rends disponible le bouton ajouter
   */
  enableAddButton: function (enabled = true) {
    this.manageAddButtonsVisibilty();
    const correspondant_checkbox = $$("input[name=medecins_rpps]");
    const addButton = $$("button[name=add_button]");
    addButton[0].disabled = true;
    if (enabled) {
      correspondant_checkbox.forEach(function (checkbox) {
        if (checkbox.checked) {
          addButton[0].disabled = false;
        }
      });
    }
  },

  /**
   * Gestion de la visibilité des boutons d'ajouts
   */
  manageAddButtonsVisibilty: function () {

    const addButton = $$("button[name=add_button]");
    const addOutOfRepositoryButton = $$("button[name=add_out_of_repository_button]");
    console.log(addButton[0].style.display);
    addButton[0].style.display = "";
    addOutOfRepositoryButton[0].style.display = "";
  },

  /**
   * Ajout de correspondant
   */
  addCorrespondant: function () {

    const correspondant_checkbox = $$("input[name=medecins_rpps]");
    let correspondantToAdd = [];
    correspondant_checkbox.forEach(function (checkbox) {
      if (checkbox.checked) {
        correspondantToAdd.push(checkbox.value)
      }
    });

    const url = new Url('dPpatients', 'addCorrespondantFromRPPS');
    url.addParam("medecins[]", correspondantToAdd, true);
    url.requestUpdate("systemMsg", {
      onComplete: function () {
        Correspondant.enableAddButton(false);
        Correspondant.refreshCorrespondantList();
      }
    });
  },

  /**
   * Mise à jour du correspondant (informations et lieux d'exercice)
   */
  updateCorrespondant: function (rpps) {
    const url = new Url('dPpatients', 'updateCorrespondant');
    url.addParam("rpps", rpps);
    url.requestUpdate("systemMsg", {
      onComplete: function () {
        Correspondant.refreshCorrespondantList();
      }
    });
  },

  refreshCorrespondantList: function () {
    var form = getForm("correspondantFilterForm");
    new Url('dPpatients', 'retrieveMatchingCorrespondantFromRPPS')
      .addFormData(form)
      .requestUpdate("listCorrespondants")
  },

  /**
   * Suppression de correspondant patient
   *
   * @param correspondant_id
   * @param view
   * @param callback
   */
  delete: (correspondant_id, view, callback) => {
    if (!confirm($T('CCorrespondantPatient-Ask delete', view))) {
      return;
    }

    new Url()
      .addParam('@class', 'CCorrespondantPatient')
      .addParam('correspondant_patient_id', correspondant_id)
      .addParam('del', 1)
      .requestUpdate(
        'systemMsg',
        {
          onComplete: callback,
          method: 'POST'
        }
      );
  }
};
