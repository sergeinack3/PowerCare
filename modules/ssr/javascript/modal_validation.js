/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ModalValidation = {
  kine_id: null,

  form: function() {
    return getForm('editSelectedEvent');
  },

  formModal: function() {
    return getForm('TreatEvents');
  },

  toggleSejour: function(sejour_id, type) {
    $('list-evenements-modal').select('input.CSejour-'+sejour_id+'.'+type).each(function(checkbox) {
      checkbox.checked = true;
      checkbox.onchange();
    });
  },
  toggleAllSejours: function(type) {
    $('list-evenements-modal').select('input[type=checkbox].'+type).each(function(checkbox) {
      checkbox.checked = true;
      checkbox.onchange();
    });
  },
  applyNbstoSeance: function(form) {
    var seance_collective_id = form.get('seance_collective_id');
    var evenement_id         = form.get('evenement_id');
    $('list-evenements-modal').select('form').each(function(old_form) {
      if (old_form.get('seance_collective_id') == seance_collective_id && old_form.get('evenement_id') != evenement_id) {
        $V(old_form.nb_patient_seance, $V(form.nb_patient_seance), true);
        $V(old_form.nb_intervenant_seance, $V(form.nb_intervenant_seance), true);
      }
    });
  },

  select: function() {
    var event_ids = [];
    var plage_groupe_ids = [];
    $$('.event.selected').each(function(e){
      var matches       = e.className.match(/CEvenementSSR-([0-9]+)/);
      var matches_plage = e.className.match(/CPlageGroupePatient-([0-9]+)/);
      if (matches) {
        event_ids.push(matches[1]);
      }

      if (matches_plage) {
        plage_groupe_ids.push(matches_plage[1]);
      }
    });

    var form = this.form();
    $V(form.event_ids, event_ids.join('|'));
    $V(form.plage_groupe_ids, plage_groupe_ids.join('|'));
    return $V(form.event_ids);
  },

  selectCheckboxes: function(in_administration) {
    // Réalisations-annulations
    var csarrs = [];
    var realise_ids = [];
    var annule_ids  = [];
    var modulateurs = [];
    var phases      = [];
    var nb_patient  = [];
    var nb_interv   = [];
    var commentaires  = [];
    var heures  = [];
    var durees  = [];
    var transmissions = [];
    var extensions = [];

    var table_actes = in_administration ? $('table_csarrs') : $('list-evenements-modal');
    table_actes.select('input[type=\'checkbox\']').each(function(checkbox) {
      if (checkbox.checked) {
        if (!in_administration && checkbox.hasClassName('realise'   )) realise_ids.push(checkbox.value);
        if (!in_administration && checkbox.hasClassName('annule'    )) annule_ids .push(checkbox.value);
        if (checkbox.hasClassName('modulateur')) modulateurs.push(checkbox.value);
        if (checkbox.hasClassName('phase'     )) phases     .push(checkbox.value);
        if (in_administration && checkbox.hasClassName('csarr')) csarrs.push(checkbox.value);
      }
    });

    table_actes.select('input[type=\'text\']').each(function(input) {
      if (!in_administration && $V(input)) {
        if (input.hasClassName('nb_patient')) nb_patient.push(input.form.get('evenement_id') + '-' + input.value);
        if (input.hasClassName('nb_interv')) nb_interv.push(input.form.get('evenement_id') + '-' + input.value);
      }
      if (input.hasClassName('commentaires')) commentaires.push(input.id + '-' + input.value);
      if (input.hasClassName('change_time') && $V(input.form._heure_deb) != input.form.get('original')) {
        heures.push(input.form.get('id_evt') + '-' + $V(input.form._heure_deb));
      }
      if (input.hasClassName('change_duree') && $V(input.form.duree) != input.form.get('original')) {
        durees.push(input.form.get('id_evt') + '-' + $V(input.form.duree));
      }
    });
    table_actes.select('textarea').each(function(input) {
      if (input.hasClassName('commentaires')) {
        commentaires.push(input.id + '-' + input.value);
      }
      else if (!in_administration && input.hasClassName('transmissions')) {
        transmissions.push(input.id + '-' + input.value);
      }
    });
    table_actes.select('select').each(function(select) {
      if (select.hasClassName('extension') && $V(select)) {
        extensions.push(select.value);
      }
    });

    var form = in_administration ? getForm('addAdministration') : this.formModal();
    if (in_administration) {
      $V(form.csarrs , csarrs.join('|'));
    }
    else {
      $V(form.realise_ids , realise_ids.join('|'));
      $V(form.annule_ids  , annule_ids .join('|'));
      $V(form.nb_patient  , nb_patient .join('|'));
      $V(form.nb_interv   , nb_interv .join('|'));
      $V(form.transmissions, transmissions.join('|'));
      $V(form.heures, heures.join('|'));
      $V(form.durees, durees.join('|'));
    }
    $V(form.modulateurs     , modulateurs.join('|'));
    $V(form.phases          , phases     .join('|'));
    $V(form.commentaires    , commentaires.join('|'));
    $V(form.extensions_doc  , extensions.join('|'));
  },

  checkedAllNbPatients: function () {
    var result_nb_interv = true;
    var result_nb_patient = true;
    $('list-evenements-modal').select('input[type=\'text\']').each(function(input) {
      if (!$V(input) ) {
        var evenement_guid = 'CEvenementSSR-'+input.form.get('evenement_id');
        var annule = null;
        $('list-evenements-modal').select('input[type=\'checkbox\']').each(function(evt) {
          if (evt.hasClassName('annule') && evt.hasClassName(evenement_guid)) {
            annule = evt;
          }
        });
        if (annule && !annule.checked) {
          //Si l'evenement n'est pas annulé
          if (input.hasClassName('nb_patient')) {
            result_nb_patient = false;
          }
          else {
            result_nb_interv = false;
          }
        }
      }
    });

    if (!result_nb_patient || !result_nb_interv) {
      alert($T('CEvenementSSR-complete_nb_patient_interv'));
    }
    return result_nb_patient && result_nb_interv;
  },

  eventCollectif: function(checked, etat, evenement_guid, seance_collective_id) {
    if (seance_collective_id) {
      var nb_patient_seance     = $('labelFor_changeNbPatient_'+evenement_guid+'_nb_patient_seance');
      var nb_intervenant_seance = $('labelFor_changeNbPatient_'+evenement_guid+'_nb_intervenant_seance');
      if (etat == 'annule' && checked) {
        var form = getForm('changeNbPatient_'+evenement_guid);
        $V(form.nb_patient_seance, '');
        $V(form.nb_intervenant_seance, '');
        nb_patient_seance.removeClassName('notNull');
        nb_intervenant_seance.removeClassName('notNull');
        nb_patient_seance.addClassName('notNullOK');
        nb_intervenant_seance.addClassName('notNullOK');
      }
      else if (etat == 'realise' && checked) {
        var form = getForm('changeNbPatient_'+evenement_guid);
        if (!$V(form.nb_patient_seance)) {
          nb_patient_seance.addClassName('notNull');
          nb_patient_seance.removeClassName('notNullOK');
        }
        if (!$V(form.nb_intervenant_seance)) {
          nb_intervenant_seance.addClassName('notNull');
          nb_intervenant_seance.removeClassName('notNullOK');
        }
      }

    }
  },

  set: function(values) {
    Form.fromObject(this.form(), values);
  },

  // Erase mode
  submit: function() {
    this.select();
    return onSubmitFormAjax(this.form(), { onComplete: function() { 
      PlanningTechnicien.show(this.kine_id, null, null, 650, true);
    } });
  },

  submitModal: function() {
    this.selectCheckboxes(false);
    if (this.checkedAllNbPatients() == true) {
      return onSubmitFormAjax(this.formModal(), function() {
        PlanningTechnicien.show(this.kine_id, null, null, 650, true);
        Control.Modal.close();
      });
    }
  },

  update: function() {
    this.select();

    var form = this.form();
    new Url('ssr', 'ajax_update_modal_evenements')
      .addParam('token_field_evts', $V(form.event_ids))
      .addParam('token_field_plages_groupe', $V(form.plage_groupe_ids))
      .addParam('kine_id', $V(getForm('selectKine').kine_id))
      .addParam('date', $V(getForm('DateSelect').date))
      .requestModal('95%', '95%');
  },

  refresh: function () {
    Control.Modal.refresh();
  },

  setVisibleField: function(name_div){
    var div = $(name_div);
    div.setVisible(div.getStyle('display') == 'none');
  },
  editCodesEvenement: function(evenement_id){
    var url = new Url('ssr', 'ajax_update_modal_evts_modif')
      .addParam('token_evts', evenement_id)
      .addParam('refresh_validation', 1)
      .requestModal(650, 400);
    modalWindow = url.modalObject;
  },

  addCodeCsarrAdministration: function(selected) {
    if (typeof selected === "object") {
      selected = selected.childElements()[0].innerHTML;
    }

    if (selected) {
      new Url('ssr', 'ajax_add_line_code_csarr')
        .addParam('code_selected', selected)
        .requestJSON(function(content) {
          $('list_csarr_administration').insert(content);
        }.bind(this));
    }
  },
  switch: function(form) {
    var input_elements = form.event_ids;
    $V(input_elements, '');
    var tab_selected = new TokenField(input_elements);
    $$(".event.selected").each(function(e){
      if(e.className.match(/CEvenementSSR-([0-9]+)/)){
        var evt_id = e.className.match(/CEvenementSSR-([0-9]+)/)[1];
        tab_selected.add(evt_id);
      }
    });
    var values = new TokenField(form.event_ids).getValues();

    // Sélection vide
    if (!values.length) {
      alert($T('CEvenementSSR-alert-selection_empty'));
      return;
    }

    return onSubmitFormAjax(form, function() {
      PlanningTechnicien.show(this.kine_id, null, null, 650, true);
      Control.Modal.close();
    } );
  }
};
