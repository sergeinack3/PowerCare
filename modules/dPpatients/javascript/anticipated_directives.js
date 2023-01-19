/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AnticipatedDirectives = {
  patient_id: null,
  number_directives: null,
  adding_directive: null,
  removing_directive: null,

  submitDirective: function (form) {
    var detenteur_id = $V(form.select_detenteur_id).split('-')[1];
    var detenteur_class = $V(form.select_detenteur_id).split('-')[0];

    $V(form.detenteur_id, detenteur_id);
    $V(form.detenteur_class, detenteur_class);

    this.adding_directive = true;

    return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
  },

  deleteDirective: function (form, obj) {
    this.removing_directive = true;
    confirmDeletion(form,{typeName:'',objName: obj, ajax:true}, {onComplete: Control.Modal.close})
  },

  /**
   * Removes the warning logo in the patient profile
   */
  removeWarningNoDirectives: function() {
    var no_directives = $$('.no-directives');
    if (no_directives.length > 0) {
      no_directives[0].parentNode.removeChild(no_directives[0]);
    }
  },

  /**
   * Adds the warning logo in the patient profile
   *
   * @param {boolean} forceDisplay
   */
  addWarningNoDirectives: function(forceDisplay = false) {
    if (this.number_directives.length === 0 || forceDisplay) {
      $$('.td-directives-anticipees')[0].innerHTML += ' <i class="fas fa-exclamation-triangle no-directives" ' +
        '                                                  style="color: #ff9502; font-size: 14px" ' +
        '                                                  title="'+$T('CDirectiveAnticipee-No directive')+'"></i>';
    }
  },

  /**
   * Add a holder for the anticipated choices of the patient
   */
  addHolder: function () {
    $$('button.add-holder')[0].observe('click', this._addHolder.bind(this));
  },
  _addHolder: function (event) {
    this.patient_id = event.target.dataset.patientId;
    Correspondant.edit(null, this.patient_id, function () {
      AnticipatedDirectives.refreshHolders(AnticipatedDirectives.patient_id);
    });
  },

  refreshHolders: function (patient_id) {
    var url = new Url("patients", "ajax_get_holders");
    url.addParam("patient_id", patient_id);
    url.requestJSON(function (json) {
      var patients_correspondants = $('patients_correspondants');
      var medical_correspondants = $('doctors_correspondants');

      var holders = Object.entries(json.holders);
      var holders_html = "";
      holders.forEach(function (holder) {
          holders_html += '<option value="'+holder[1]['guid']+'">'+holder[1]['view']+'</option>';
      });
      patients_correspondants.innerHTML = holders_html;

      var medical_holders_html = "";
      var medical_holders = Object.entries(json.medical_holders);
      medical_holders.forEach(function (medical_holder) {
        medical_holders_html += '<option value="'+medical_holder[1]['guid']+'">'+medical_holder[1]['view']+'</option>';
      });
      medical_correspondants.innerHTML = medical_holders_html;

    });
  }
};