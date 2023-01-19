/**
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatientUnmerge = window.PatientUnmerge || {
  old_patient_id:      '',
  new_patient_id:      '',
  tabs_view:           [],
  nb_tabs:             0,
  backrefs_check:      [],
  nb_backrefs:         0,
  empty_back_visible:  false,
  empty_field_visible: false,


  _init: function (nb_tabs, nb_backrefs, old_patient_id, new_patient_id) {
    PatientUnmerge.tabs_view = [];
    PatientUnmerge.backrefs_check = [];
    PatientUnmerge.empty_visible = false;

    PatientUnmerge.nb_tabs = nb_tabs;
    PatientUnmerge.nb_backrefs = nb_backrefs;
    PatientUnmerge.old_patient_id = old_patient_id;
    PatientUnmerge.new_patient_id = new_patient_id;
  },

  /**
   * Show the unmerge modal
   *
   * @param patient_id ID of the patient to unmerge
   */
  showUnmergePatient: function (patient_id) {
    var url = new Url('dPpatients', 'vw_unmerge_patient');
    url.addParam('patient_id', patient_id);

    url.requestModal('60%', '80%');
  },

  /**
   * Get the new patient id
   *
   * @param id New patient id
   */
  createPatientCallback: function (id) {
    if (!id || !PatientUnmerge.old_patient_id) {
      return;
    }

    Control.Modal.close();

    var url = new Url('dPpatients', 'vw_unmerge_backprops');
    url.addParam('new_patient_id', id);
    url.addParam('old_patient_id', PatientUnmerge.old_patient_id);
    url.requestModal('40%', '60%', {showClose: false});
  },

  /**
   * Display or hide empty backprops category
   */
  showEmptyBackprops: function () {
    if (PatientUnmerge.empty_back_visible) {
      $$('li.empty-backprop-line').each(function (elt) {
        elt.hide();
        PatientUnmerge.empty_back_visible = false;
      });
    } else {
      $$('li.empty-backprop-line').each(function (elt) {
        elt.show();
        PatientUnmerge.empty_back_visible = true;
      });
    }
  },

  /**
   * Load tab if it's selected for the first time.
   * Count the number of tabs loaded to know if all have been seen
   *
   * @param container Container function is called for
   */
  loadTab: function (container) {
    if (container === false) {
      return;
    }

    var back_name = container.id.split('-').pop();

    if (!PatientUnmerge.tabs_view.include(container.id)) {
      PatientUnmerge.tabs_view.push(container.id);

      var url = new Url('dPpatients', 'ajax_unmerge_backprops');
      url.addParam('old_patient_id', PatientUnmerge.old_patient_id);
      url.addParam('new_patient_id', PatientUnmerge.new_patient_id);
      url.addParam('backprop_name', back_name);
      url.requestUpdate('tab-backprop-' + back_name);
    }
    if (PatientUnmerge.tabs_view.length >= PatientUnmerge.nb_tabs
      && PatientUnmerge.backrefs_check.length >= PatientUnmerge.nb_backrefs) {
      $('save_all_backprops').enable();
    }
  },

  /**
   * Display a popup to show a field history
   *
   * @param patient_id Patient id we want history of
   * @param field_name name of the field we want history of
   */
  fieldHistory: function (patient_id, field_name) {
    var url = new Url("dPpatients", "vw_field_history");
    url.addParam("object_class", 'CPatient');
    url.addParam("object_id", patient_id);
    url.addParam("field_name", field_name);
    url.popup(300, 500, "history");
  },

  /**
   * Display or hide the empty patient fields
   */
  showEmptyFields: function () {
    if (PatientUnmerge.empty_field_visible) {
      $$('tr.none').each(function (elt) {
        elt.hide();
        PatientUnmerge.empty_field_visible = false;
      });
    } else {
      $$('tr.none').each(function (elt) {
        elt.show();
        PatientUnmerge.empty_field_visible = true;
      });
    }
  },

  /**
   * Count how many radio buttons avec been checked
   *
   * @param radio_id The radio button id
   */
  countRadioClick: function (radio_id) {
    if (!radio_id || PatientUnmerge.backrefs_check.include(radio_id)) {
      return;
    }

    PatientUnmerge.backrefs_check.push(radio_id);

    if (PatientUnmerge.backrefs_check.length >= PatientUnmerge.nb_backrefs
      && PatientUnmerge.tabs_view.length >= PatientUnmerge.nb_tabs) {
      $('save_all_backprops').enable();
    }
  },

  /**
   * Abort an unmerge. Set the field 'abort' to 1 for the load-all-backprops form
   *
   * @returns {Boolean}
   */
  abortUnmerge: function (form) {
    $V(form.elements.abort, '1');

    return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
  },

  /**
   * Create a modal to ask for unmerge confirmation
   *
   * @param form The form to submit
   */
  confirmPatientUnmerge: function (form) {
    Modal.confirm(
      $T('mod-dPpatients-confirm-unmerge this patient?'),
      {
        onOK: function () {
          return form.onsubmit();
        }
      }
    );
  },

  confirmPatientCreate: function (form) {
    Modal.confirm(
      $T('mod-dPpatients-confirm-create this patient?'),
      {
        onOK: function () {
          return form.onsubmit();
        }
      }
    );
  },

  refreshOldPatient: function () {
    var url = new Url('dPpatients', 'ajax_vw_unmerge_patient');
    url.addParam('patient_id', PatientUnmerge.old_patient_id);
    url.addParam('step', 'old');
    url.requestUpdate('old-patient-infos');
  }
};