/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

PlageConsultSelector = {
  is_multiple      : false,
  DHE              : false,
  sForm            : null,
  sHeure           : null,
  sPlageconsult_id : null,
  sDate            : null,
  sChir_id         : null,
  sChir_view       : null,
  sFunction_id     : null,
  sDatePlanning    : null,
  sConsultId       : null,
  multipleMode     : 0,
  multipleEdit     : 0,
  options          : {},
  pages            : [],
  consultations    : {},
  sLineElementId   : null,

    modal: function() {
      var oForm = getForm(this.sForm);
      var chir_id = $V(oForm[this.sChir_id]);
      var function_id = $V(oForm[this.sFunction_id]);
      var heure = $V(oForm[this.sHeure]);

      // no chir, no function = heavy load
      if (!chir_id && !function_id) {
        if (!confirm("Vous n'avez pas selectionné de praticien ni de cabinet, voulez-vous continuer ?")) {
          return;
        }
      }

      var url = new Url("dPcabinet", "plage_selector");
      url.addParam("chir_id"        , chir_id);
      url.addParam("function_id"    , function_id);
      url.addParam("plageconsult_id", $V(oForm[this.sPlageconsult_id]));
      url.addParam("heure"          , heure);                               // first plage prise
      url.addParam("multipleMode"       , (this.multipleMode === 0 || !this.multipleMode) ? 0 : 1);
      url.addParam("_line_element_id", $V(oForm[this.sLineElementId]));
      url.addParam("consultation_id", $V(oForm[this.sConsultId]));
      if (this.multipleEdit) {
        url.addParam("multipleEdit", this.multipleEdit);
        url.addParam("hide_finished", 0);
      }
      if (this.sDatePlanning != null && $V(oForm[this.sDatePlanning])) {
        url.addParam("date", $V(oForm[this.sDatePlanning]));
      }
      url.modal(this.options);
    },

  updateFromSelector : function() {
    if (!this.consultations.size()) {
      console.log("error, pas de plages du selecteur");
      return;
    }
    PlageConsultSelector.resetRDVForms();

    var oForm = getForm(window.PlageConsultSelector.sForm);
    var iterator = 0;
    this.consultations.each(function(elt) {
      var consult = elt;

      // main consult
      if (iterator == 0) {
        window.PlageConsultSelector.set(consult.heure, consult.plage_id, consult.date, consult.chir_id, consult.is_cancelled, consult._chirview);
        PlageConsultSelector.listResources(consult.chir_id, consult.consult_id, consult.date, consult.heure);
      }
      // multiple
      else {
        $V(oForm["consult_multiple"], '1');
        $V(oForm["plage_id_"+iterator], consult.plage_id);
        $V(oForm["date_"+iterator], consult.date);
        $V(oForm["heure_"+iterator], consult.heure);
        $V(oForm["chir_id_"+iterator], consult.chir_id);
        $V(oForm["consult_id_"+iterator], consult.consult_id);
        $V(oForm["cancel_"+iterator], consult.is_cancelled);
        $V(oForm["rques_"+iterator], consult.rques);
        $V(oForm["_consult"+iterator], consult._chirview+" le "+DateFormat.format(new Date(consult.date), "dd/MM/yyyy")+" à "+consult.heure);
        $V(oForm["element_prescription_id_"+iterator], consult.el_prescrip_id);

        if (consult.el_prescrip_libelle) {
          $V(oForm["libelle_"+iterator], consult.el_prescrip_libelle);
        }

        if ($V(oForm["_consult"+iterator])) {
          $("place_reca_"+iterator).show();
        }
      }
      iterator++;
    });
    // close the modal (there is at least one consult)
    window.Control.Modal.close();

    // if fast RDV, I pop the patselector
    if (Preferences.choosePatientAfterDate == 1 && !$V(oForm["patient_id"]) && !oForm._pause.checked) {
      window.Control.Modal.close();
      window.PatSelector.init();
    }
  },

  set: function(heure, plage_id, date, chir_id, is_cancelled, chir_view) {
    if (this.DHE) {
      this.setForDHE(heure, plage_id, date, chir_id, is_cancelled, chir_view);
    }
    else {
      this.setForSingleConsult(heure, plage_id, date, chir_id, is_cancelled, chir_view)
    }
  },

  resetRDVForms: function () {
    Array.from($$("[id*=place_reca_]")).invoke('hide');
    Array.from($$("[id*=place_reca_] input")).forEach(function (elt) {
      elt.value = '';
    });
  },

  /**
   * Set the plage data for the single consultation view
   */
  setForSingleConsult: function (heure, plage_id, date, chir_id, is_cancelled, chir_view) {
    var oForm = getForm(this.sForm);

    var chir_id_old = $V(oForm[this.sChir_id]);
    var refresh_function = !chir_id_old || (chir_id_old != chir_id);

    $V(oForm[this.sChir_id] , chir_id);
    oForm[this.sChir_id].fire("ui:change");

    if (chir_id) {
      refreshListCategorie(chir_id, $V(oForm.categorie_id));
      // On ne rafraîchit la liste des fonctions que si le praticien n'a pas été saisi précédemment
      // ou qu'il est différent
      if (refresh_function) {
        refreshFunction(chir_id);
      }
      $V(oForm[this.sFunction_id], '');
    }
    $V(oForm.annule          , (parseInt(is_cancelled) == 1) ? 1 : 0);
    $V(oForm[this.sHeure]          , heure);
    var clean_date = (date.indexOf(" ") != -1) ? date : DateFormat.format(Date.fromDATE(date), "dd/MM/yyyy");
    $V(oForm[this.sDatePlanning], date);
    $V(oForm[this.sDate], clean_date);
    $V(oForm[this.sPlageconsult_id], plage_id, true);
  },

  /**
   * Set the plage data for the new DHE
   */
  setForDHE: function(heure, plage_id, date, chir_id, is_cancelled, chir_view) {
    var form = getForm(this.sForm);

    /* what the heck is this thing with the functions?? */
    $V(form.elements['annule'], parseInt(is_cancelled) == 1) ? 1 : 0;
    $V(form.elements[this.sDate], Date.fromDATETIME(date + ' ' + heure).toLocaleDateTime());
    $V(form[this.sHeure], heure);
    $V(form.elements[this.sPlageconsult_id], plage_id);

    if (chir_view && this.sChir_view) {
      $V(form[this.sChir_view], chir_view);
    }

    $V(form[this.sChir_id], chir_id, true);
    form[this.sChir_id].fire("ui:change");
  },

  removeConsult : function(plage_id) {
    if(this.consultations[plage_id]) {
      delete this.consultations[plage_id];
    }
  },

  updateNbRdvs: function () {
    $$('input[name=nb_rdvs]')[0].value = RDVmultiples.slots.length;
  },

  guessNexts: function (multiple_mode) {
    if (RDVmultiples.slots && !PlageConsultSelector.checkTimePicked()) {
      return;
    }
    var oform = getForm("Filter");
    if (!$V(oform.repeat_type) || !$V(oform.repeat_number)) {
      return;
    }

    var hour = null;
    try {
      var hour = document.querySelector('.plage_rank .selected').parentNode.querySelector('input[type="radio"]:checked').dataset.time;
    }
    catch (e) {}
    var url = new Url("cabinet", "ajax_guess_next");
    url.addParam('type', $V(oform.repeat_type));
    url.addParam('number', $V(oform.repeat_number));
    url.addParam('function_id', $V(oform._function_id));
    url.addParam('chir_id', $V(oform.chir_id));
    var consults = [];

    var max = ($V(oform.nb_rdvs) > RDVmultiples.slots.length) ? RDVmultiples.slots.length : $V(oform.nb_rdvs);

    for (var i=0; i<max; i++) {
      if (RDVmultiples.slots[i].date) {
        consults.push(RDVmultiples.slots[i].date);
      }
    }
    if (!consults.length) {return;}
    url.addParam('dates[]', consults, true);
    url.requestJSON(function(elts) {
      if (!elts.length) {
        return;
      }

      var first_unknown = 0;
      var position = max;
      var time_seq = $V(oform.nb_rdvs);
      var time_seq_i = 0;
      for (var a = 0; a < elts.length; a++) {
        var data = elts[a];
        RDVmultiples.selRank(position);

        // if we have a plage_id
        if (elts[a].indexOf("-") === -1) {
          RDVmultiples.loadPlageConsult(data, null, true, RDVmultiples.slots[time_seq_i].heure);
        }
        else {
          if (!first_unknown) {
            first_unknown = position;
          }
          var form = getForm("Filter");
          var url = new Url("cabinet", "ajax_list_plages");
          url.addParam("dialog"             , 1);
          if (!$V(form._function_id)) {
            url.addParam("chir_id"            , $V(form.chir_id));
          }
          url.addParam("multipleMode"       , multiple_mode);
          url.addParam("period"             , $V(form.repeat_type));
          url.addParam("date"               , data);
          url.addParam("hour"               , RDVmultiples.slots[time_seq_i].heure);
          url.addParam("hide_finished"      , $V(form.hide_finished));
          url.addParam("function_id"        , $V(form._function_id));
          url.addParam("as_place"           , 1);
          url.requestUpdate('listPlaces-'+position);
        }
        RDVmultiples.selRank(first_unknown);
        position++;
        time_seq_i = (time_seq_i < time_seq-1) ? time_seq_i+1 : 0;
      }
    });
  },

  previous_plage: function (plage_id, dom_button) { this.switchAndLoadPlage(plage_id, dom_button); },
  next_plage: function (plage_id, dom_button) { this.switchAndLoadPlage(plage_id, dom_button); },

  switchAndLoadPlage: function (plage_id, dom_button) {
    RDVmultiples.selRank($(dom_button).up('div.plage_rank').get("slot_number"));
    RDVmultiples.loadPlageConsult(plage_id, null, true);
  },

  changePlageChir: function(chir_id, date, multiple_mode) {
    var form = getForm("Filter");

    var url = new Url("cabinet", "ajax_list_plages");
    url.addParam("dialog"             , 1);
    url.addParam("chir_id"            , chir_id);
    url.addParam("multipleMode"       , (multiple_mode) ? 1 : 0);
    url.addParam("period"             , (multiple_mode) ? $V(form.repeat_type) : $V(form.period));
    url.addParam("hide_finished"      , $V(form.hide_finished));
    url.addParam("date"               , date);
    url.addParam("as_place"           , 1);
    url.requestUpdate('listPlaces-0');
  },

  loadPlageConsult : function(plageconsult_id, listPlace_id) {
    listPlace_id = listPlace_id ? listPlace_id : 0;
    $$("div[id^='listPlaces-']").invoke('hide');
    $('listPlaces-'+listPlace_id).show();

    new Url("dPcabinet", "httpreq_list_places")
      .addParam("plageconsult_id", plageconsult_id)
      .addParam("slot_id", "0")
      .requestUpdate("listPlaces-"+listPlace_id);
  },

  /**
   * Load the list of resources of a function
   *
   * @param {int} practitioner_id
   * @param {int} appointment_id
   * @param {string} day
   * @param {string} hour
   */
  listResources: function(practitioner_id, appointment_id, day, hour) {
    var resources_div = $('resources-list');
    resources_div = (resources_div) ? resources_div : parent.$('resources-list');

    if (!resources_div) {
      return;
    }

    new Url('cabinet', 'ajax_load_resources')
      .addParam('practitioner_id', practitioner_id)
      .addParam('appointment_id', appointment_id)
      .addParam('date', day)
      .addParam('hour', hour)
      .requestUpdate(resources_div);
  },

  checkTimePicked: function () {
    var return_val = true;
    $$('.plage_rank').forEach(function (plage) {
      if (plage.querySelector('.listPlace').innerHTML) {
        if (!plage.querySelector('.listPlace').contains(plage.querySelector('input[type=radio]'))) {
          return_val = false;
        }
        else {
          var checked = Array.from(plage.querySelectorAll('.listPlace input')).some(function(input) {
            return input.checked;
          });
          return_val = (return_val) ? checked : return_val;
        }
      }
    });

    if (!return_val) {
      alert($T('CPlageConsult-please-select-time'));
      return false;
    }
    return true;
  },

  updatePlage: function(multiple_mode, sdate, callback) {
    var form = getForm("Filter");
    if ($V(form.period) == "weekly") {
      form.submit();
    }
    var url = new Url("cabinet", "ajax_list_plages");
    url.addParam("dialog"             , 1);
    url.addParam("function_id"        , $V(form._function_id));
    if (!$V(form._function_id)) {
      url.addParam("chir_id"            , $V(form.chir_id));
    }
    url.addParam("plageconsult_id"    , $V(form.plageconsult_id));
    url.addParam("consultation_id"    , $V(form.consultation_id));
    url.addParam("_line_element_id"   , $V(form._line_element_id));
    url.addParam("period"             , $V(form.period));
    url.addParam("multipleMode"       , multiple_mode);
    url.addParam("hour"               , $V(form.hour));
    url.addParam("date"               , sdate ? sdate : $V(form.date));
    url.addParam("hide_finished"      , $V(form.hide_finished));
    url.addParam("function_id"       , $V(form._function_id));
    url.requestUpdate('listePlages', callback);
  },

  sendData : function(plage_id, consult_id, date, time, chir_id, _chir_view, el_prescrip_id, el_prescrip_libelle) {
    window.parent.PlageConsultSelector.consultations = [new consultationRdV(plage_id, consult_id, date, time, chir_id, _chir_view, el_prescrip_id, el_prescrip_libelle)];
    window.parent.PlageConsultSelector.updateFromSelector();

    PlageConsultSelector.listResources(chir_id, consult_id, date, time);

    var form_filter = getForm("Filter");
    var form_consult = window.parent.getForm("editFrm");

    if (form_filter.nb_semaines && form_consult.nb_semaines) {
      $V(form_consult.nb_semaines, $V(form_filter.nb_semaines));
    }
  }
};