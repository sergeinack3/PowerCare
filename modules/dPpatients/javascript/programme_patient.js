/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Programme = {
  editProgramme: function (programme_id) {
    new Url("patients", "ajax_edit_programme")
      .addParam("programme_id", programme_id)
      .requestModal("30%", null, {onClose: Control.Modal.refresh});
  },

  showPatientProgramme: function (programme_id) {
    new Url("patients", "ajax_patient_programme")
      .addParam("programme_id", programme_id)
      .requestModal("30%", null);
  },

  /**
   * Select the year to filter the protocol inclusions based on it's beginning date
   *
   * @param {HTMLElement} element
   */
  selectYear: function (element) {
    new Url('patients', 'vw_programmes')
      .addParam('year', element.value)
      .requestUpdate('programmes');
  }
};

RegleEvt = {
  editRegle:      function (regle_id) {
    new Url("patients", "editRegleAlerteEvt")
      .addParam("regle_id", regle_id)
      .requestModal("50%", null, {onClose: Control.Modal.refresh});
  },
  createSpanCIM:  function (value, field) {
    if (!value) {
      return;
    }
    let span = DOM.span({'className': field}, value);
    let _line = DOM.span({'className': 'tag_tab'}, span);
    let del = DOM.span({
      'style':     'margin-right:5px;float:left;',
      'className': 'fas fa-trash-alt',
      'title':     'Supprimer',
      'onclick':   'this.up().remove();'
    });
    _line.insert(del);
    $('codes_cim_regle_alerte_' + field).insert(_line);
    $V(getForm('edit_program').elements['keywords_code_' + field], '');
  },
  compactCodeCIM: function () {
    let form = getForm('edit_program');
    let codes_cim = [], pathologies = [];
    form.select('span[class=diagnostic]').each(function (elt) {
      codes_cim.push(elt.innerHTML);
    });
    form.select('span[class=pathologie]').each(function (elt) {
      pathologies.push(elt.innerHTML);
    });
    $V(form.diagnostics, codes_cim.join('|'));
    $V(form.pathologies, pathologies.join('|'));
    form.onsubmit();
  }
};
