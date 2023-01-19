/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

IdentityValidator = window.IdentityValidator || {
  active:            false,
  patient_id:        null,
  callback:          null,
  managing_identity: false,

    manage: function (status, patient_id, callback) {
        if (IdentityValidator.managing_identity) {
          return;
        }

        IdentityValidator.managing_identity = true;

    IdentityValidator.patient_id = patient_id;
    IdentityValidator.callback = callback;

        if (!IdentityValidator.active || status !== 'VIDE') {
            IdentityValidator.managing_identity = false;
            return IdentityValidator.callback();
        }

    new Url('patients', 'ajax_manage_identity')
      .addParam('patient_id', IdentityValidator.patient_id)
      .requestModal('70%', null, {
        onClose: () => {
          IdentityValidator.managing_identity = false;
        }
      });
  },

  submitForm: function (form) {
    onSubmitFormAjax(
      form, (function () {
        Control.Modal.close();
        IdentityValidator.callback();
      }).bind(this)
    );
  },

  validateIdentity: function () {
    Patient.editModal(this.patient_id, null, 'window.parent.IdentityValidator.callbackStorePatient', null, null, 1);
  },

  callbackStorePatient: function (id, patient) {
    if (patient.status !== 'VIDE') {
      Control.Modal.close();
      IdentityValidator.callback();
    }
  },

  merge: function (link, status) {
    var patient_ids_doubloons = getForm('getDoubloons').select('input[name=\'patient_id[]\']:checked');
    var patient_ids_links = getForm('getLinks').select('input[name=\'patient_id[]\']:checked');

    Control.Modal.close();

    new Url('patients', 'ajax_merge_link_patients')
      .addParam('patient_id', this.patient_id)
      .addParam('patient_ids_doublooons[]', patient_ids_doubloons.pluck('value'), true)
      .addParam('patient_ids_links[]', patient_ids_links.pluck('value'), true)
      .addParam('status', status)
      .addParam('link', link)
      .requestModal('60%');
  },

  link: function (status) {
    this.merge(1, status);
  },

  toggleActionButton: function () {
    var buttons = $('manage_identity').select('button.big');

    var active = getForm('getDoubloons').select('input[name=\'patient_id[]\']:checked').length
      + getForm('getLinks').select('input[name=\'patient_id[]\']:checked').length;

    buttons.invoke('writeAttribute', 'disabled', active ? null : 'disabled');
  }
};
