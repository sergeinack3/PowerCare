/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Seance = {
  jsonSejours: {},
  jsonSSR: {},
  checked: 0,
  selectPatient: function(form) {
    new Url('ssr', 'ajax_patients_seance_collective')
      .addFormData(form)
      .requestModal('90%', '90%');
  },
  checkCountSejours: function(type) {
    var type_checkbox = type ? type : 'sejour';
    var checked   = 0;
    var count     = 0;
    getForm('select_'+type_checkbox+'_collectif').select('input[class='+type_checkbox+'_collectif]').each(function(e){
      count++;
      if ($V(e)) { checked ++; }
    });
    var element = $('select_'+type_checkbox+'_collectif_check_all_'+type_checkbox+'s');
    element.checked = '';
    element.style.opacity = '1';
    if (checked > 0) {
      element.checked = 'checked';
      $V(element, 1);
      if (checked < count) {
        element.style.opacity = '0.5';
      }
    }
    Seance.checked = checked;

    if (type_checkbox == 'evt') {
      $('add_select_evt_collectif').disabled = checked ? '' : 'disabled';
    }
  },
  selectSejours: function(valeur, type){
    var type_checkbox = type ? type : 'sejour';
    getForm('select_'+type_checkbox+'_collectif').select('input[class='+type_checkbox+'_collectif]').each(function(e){
      $V(e, valeur);
    });
  },
  addSejour: function(){
    var form = getForm('editEvenementSSR');
    $V(form._sejours_guids, Object.toJSON(Seance.jsonSejours));
    var button_seance = $('seance_collective_add_patient');
    button_seance.className = 'info';
    button_seance.innerHTML = Seance.checked + ' ' + $T('ssr-patient_selected');
    Planification.checkPlanificationPatient(form);
    Control.Modal.close();
  },
  eventsSejour: function(sejour_id){
    new Url('ssr', 'vw_list_events_sejour')
      .addParam('sejour_id', sejour_id)
      .popup(1000, 600, $T('mod-ssr-tab-vw_list_events_sejour'));
  },
  gestionPatients: function(evenement_ssr_id) {
    new Url('ssr', 'vw_gestion_patients_collectifs')
      .addParam('evenement_ssr_id', evenement_ssr_id)
      .requestModal('50%', '50%');
  },
  deletePatientsCollectif: function(form) {
    if (confirm($T('CEvenementSSR-delete-confirmation'))) {
      $V(form.evts_to_delete, Object.toJSON(Seance.jsonSSR));
      onSubmitFormAjax(form, {
        onComplete: function() {
          document.location.reload();
        }
      });
    }
  },
  selectAllLines : function(element) {
    var valeur = $V(element);
    element.form.select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('seance_patient-') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  },
  showCheckbox: function(seance_collective_guid) {
    var checked   = 0;
    var count     = 0;
    getForm('deletePatientsCollectif-'+seance_collective_guid).select('input,checkbox').each(function(e){
      if (e.name.indexOf('seance_patient-') >= 0) {
        count++;
        if ($V(e)) { checked ++; }
      }
    });

    var input_title = $('check_all_sejours_ssr');
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
  showEvtsCollectifsDispo: function(sejour_id) {
    new Url('ssr', 'vw_seances_collectives_dispo')
      .addParam('sejour_id', sejour_id)
      .requestModal('90%', '90%');
  },
  editCodesEvtsToPatient: function() {
    new Url('ssr', 'vw_edit_evts_collectif_choose')
      .addParam('sejour_id', $V(getForm('select_evt_collectif').sejour_id))
      .addParam('evts_ids', Object.toJSON(Seance.jsonSejours))
      .requestModal('90%', '90%');
  },
  createEvtsCollectifsCodes: function(sejour_id, use_no_acte) {
    var submit_possible = 0;
    var formulaires = $('choose_codes_seances_collectives').select('form');
    formulaires.each(function(form){
      if (form.select('input.checkbox-other-prestas_ssr').length || form.select('input.checkbox-prestas_ssr:checked').length
      || form.select('input.checkbox-other-csarrs').length || form.select('input.checkbox-csarrs:checked').length) {
        submit_possible ++;
      }
    });
    if (submit_possible == formulaires.length || use_no_acte == 'aucun') {
      formulaires.each(function(form){
        onSubmitFormAjax(form);
      });
      Control.Modal.close();
      Control.Modal.close();
      Planification.refresh(sejour_id);
    }
    else {
      alert($T('CEvenementSSR-choose_code_obligatory'))
    }
  },
  sortBy: function (order_col, order_way) {
    new Url($V(getForm('select_sejour_collectif').m), 'ajax_patients_seance_collective')
      .addParam('order_col', order_col)
      .addParam('order_way', order_way)
      .requestUpdate('select_sejour_collectif');
  },
  confirmAnnulationEvt: function(form) {
    if (confirm($T('CEvenementSSR-cancel_selected-confirm'))) {
      $V(form.evts_to_delete, Object.toJSON(Seance.jsonSejours));
      form.onsubmit();
    }
  }
};
