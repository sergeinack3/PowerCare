/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PersonnelSejour = {
  interval: null,

  // Initialisation du tableau des sejours json
  jsonSejours: {},
  editTiming: function(timing_id) {
    var url = new Url('soins'  , 'vw_edit_timing_personnel');
    url.addParam('timing_id', timing_id);
    url.requestModal(300, null,
      {onClose: function () {PersonnelSejour.refreshListTimings();}}
    );
  },
  refreshListTimings: function() {
    var url = new Url('soins'  , 'vw_timings_affectation_sejour');
    url.requestUpdate("list_timings_personnel_sejour");
  },
  /**
   * Refresh the list of caregivers
   *
   * @param list_only
   */
  refreshListeSoignant : function(list_only) {
    var form = getForm('filtresSoignants');
    var url = new Url('soins', 'vw_affectations_soignant');
    url.addFormData(form);
    if (list_only) {
      url.addParam("list_only", 1);
    }
    url.requestUpdate('liste_soignants', window.ImedsResultsWatcher ? ImedsResultsWatcher.loadResults : Prototype.emptyFunction);
  },
  showMacrocibles : function(sejour_guid) {
    var url = new Url('hospi', 'vw_macrocibles');
    url.addParam('object_guid', sejour_guid);
    url.requestModal();
  },
  showRegime : function(prescription_guid) {
    var url = new Url('soins', 'vw_elts_regime_sejour');
    url.addParam('object_guid', prescription_guid);
    url.requestModal();
  },
  gestionMultiplePersonnel : function(service_id, date) {
    var url = new Url("planningOp", "vw_affectations_multiple_personnel");
    url.addParam('sejour_ids', Object.toJSON(this.jsonSejours[service_id]));
    url.addParam('service_id', service_id);
    url.addParam('date', date);
    url.requestModal('80%', '80%',
      {onClose: function () {PersonnelSejour.refreshListeSoignant();}}
    );
  },
  submitMultiAffectations : function(form) {
    return onSubmitFormAjax(form, {
      onComplete: function() {
        Control.Modal.refresh();
      }
    });
  },
  selectAllSejours : function(valeur) {
    getForm('filtresSoignants').select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('CSejour-') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  },
  selectAllSejoursByService : function(element) {
    var valeur = $V(element);
    var sejoursByService = element.up('.sejours_by_service');
    sejoursByService.select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('CSejour-') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  },
  addPersonnel : function(id) {
    users_personnel.add(id);
    PersonnelSejour.checkValidityForm();
  },
  removePersonnel : function(id, element) {
    users_personnel.remove(id);
    element.remove();
    PersonnelSejour.checkValidityForm();
  },
  checkValidityForm : function() {
    var form = getForm(affectationMultiplePersonnel);
    $('submit_affectations_multiples').disabled = $V(form.ids_personnel) ? '' : 'disabled'
  },
  showCheckSejours : function() {
    var checked   = 0;
    var count     = 0;
    getForm('filtresSoignants').select('input,checkbox').each(function(e){
      if (e.name.indexOf('CSejour-') >= 0) {
        count++;
        if ($V(e)) { checked ++; }
      }
    });

    var input_title = $('check_all_sejours');
    input_title.checked = '';
    input_title.style.opacity = '1';
    if (checked > 0) {
      input_title.checked = 'checked';
      $V(input_title, 1);
      if (checked < count) {
        input_title.style.opacity = '0.5';
      }
    }
  },
  changeDatesUserSejour : function() {
    var form_dates = getForm('affectationMultiplePersonnel');
    var time_debut = $V(form_dates.debut).split(" ")[1];
    var time_fin = $V(form_dates.fin).split(" ")[1];
    $V(form_dates.debut,    $V(form_dates._debut)+' '+time_debut);
    $V(form_dates.fin,      $V(form_dates._fin)+' '+time_fin);
  },
  changePeriodUserSejour : function(debut, fin) {
    var form_dates = getForm('affectationMultiplePersonnel');
    $V(form_dates.debut,    $V(form_dates._debut)+' '+debut);
    $V(form_dates.fin,      $V(form_dates._fin)+' '+fin);
  },
  delUserMultiAffectation : function(id_sejour_affectation) {
    if (id_sejour_affectation) {
      var form = getForm('delUserMultiAffectation');
      $V(form.sejour_affectation_id, id_sejour_affectation);
      return onSubmitFormAjax(form, {
        onComplete: function() {
          Control.Modal.refresh();
        }
      });
    }
  },
  refreshListSejours : function(with_old) {
    var url = new Url("planningOp", "vw_affectations_multiple_personnel");
    url.addParam('with_old' , with_old);
    url.addParam('only_list', 1);
    url.requestUpdate('list_sejours_affectations_multiples');
  },

  selectServices: function () {
    new Url("hospi", "ajax_select_services")
      .addParam("ajax_request", 0)
      .addParam("view", "personnel")
      .addParam("callback", "PersonnelSejour.refreshListeSoignant()")
      .requestModal(null, null, {maxHeight: "90%"});
  },

  addSejourJson: function (guid, service_id) {
    var json = {
      line_guid : guid,
      _checked : 0 };
    if (!this.jsonSejours[service_id]) {
      this.jsonSejours[service_id] = {};
    }
    this.jsonSejours[service_id][guid] = json;
  },

  changeSejourJson: function (checkbox, guid, service_id) {
    this.jsonSejours[service_id][guid]._checked = (checkbox.checked ? 1 : 0);
  },
};