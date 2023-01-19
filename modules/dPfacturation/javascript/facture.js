/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

window.Facture = {
  evenement_guid: null,
  evenement_id: null,
  user_id: null,
  saveNoRefresh: function(form) {
    return onSubmitFormAjax(form);
  },
  reload: function(patient_id, consult_id ,not_load_banque, facture_id, object_class) {
    if (!$('load_facture')) {
      Facture.reloadFactureModal(facture_id, object_class);
    }
    else {
      var url = new Url('facturation', 'ajax_view_facture');
      url.addParam('patient_id'      , patient_id);
      url.addParam('consult_id'      , consult_id);
      url.addParam('object_class'    , object_class);
      url.addParam('not_load_banque' , not_load_banque);
      url.addParam('facture_id'      , facture_id);
      url.requestUpdate('load_facture');
    }
  },
  modifCloture: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : this.callbackModif.curry(form)
    });
  },
  callbackModif: function(form, factureId, factureClass) {
    if (!$('load_facture')) {
      // Reload the consultation's facture container
      Control.Modal.refresh();
    }
    else if ($('facturation')) {
      Reglement.reload();
    }
    else {
      factureId = factureId ? factureId : $V(form.facture_id);
      factureClass = factureClass ? factureClass : $V(form.facture_class);
      // Refresh the facture modal
      Facture.reloadFactureModal(factureId, factureClass, $('load_facture') ? 'load_facture' : null);
    }
  },
  extourne: function(form) {
    this.annule(form, 1);
  },
  annule: function(form, duplicate) {
    if (!confirm($T('CFacture-confirm ' + (duplicate ? 'extourne' : 'annule')))) {
      return false;
    }
    $V(form._duplicate, duplicate ? 1 : 0);
    $V(form.annule, duplicate ? 0 : 1);
    return onSubmitFormAjax(form, {
      onComplete : function() {
        if ($('a_reglements_consult')) {
          Reglement.reload();
        }
        else if ($('a_reglements_evt')) {
          $('a_reglements_evt').onmousedown();
        }
        else {
          Facture.reloadFactureModal($V(form.facture_id), $V(form.facture_class), $('load_facture') ? 'load_facture' : null);
        }
      }
    });
  },
  reloadReglement: function(facture_id, facture_class) {
    var url = new Url('facturation', 'ajax_refresh_reglement');
    url.addParam('facture_id'    , facture_id);
    url.addParam('facture_class' , facture_class);
    url.requestUpdate('reglements_facture');
    if (!$('load_facture')) {
      Facture.reloadFactureModal(facture_id, facture_class);
    }
  },
  cut: function(form) {
    onSubmitFormAjax(form, {
      onComplete : function() {
        if (!$('load_facture')) {
          Facture.reloadFactureModal($V(form.facture_id), $V(form.facture_class));
        }
        else {
          var url = new Url('facturation', 'ajax_view_facture');
          url.addElement(form.facture_id);
          url.addParam('object_class'  , $V(form.facture_class));
          url.requestUpdate("load_facture");
        }
      }
    });
  },
  edit: function(facture_id, facture_class, show_button, refreshAfterClose) {
    show_button = show_button || 1;
    var url = new Url('facturation', 'ajax_view_facture');
    url.addParam('facture_id'    , facture_id);
    url.addParam("object_class", facture_class);
    url.addParam("show_button", show_button);
    url.requestModal('90%', '90%');
    if (refreshAfterClose) {
      url.modalObject.observe("afterClose", Control.Modal.refresh);
    }
  },

  checkDocClose: function(url, form, facture_class, facture_id, type_pdf) {
    if (url.oWindow.closed) {
      if (confirm($T('CFacture.confirm_bill_print_update'))) {
        var field = type_pdf === "justificatif" ? "justif_" : "bill_";
        var updateForm = DOM.form(
          {
            method: 'post',
            action: '',
            name: 'ajax_save_facture'
          },
          DOM.input({value: facture_id,    name: 'facture_class'}),
          DOM.input({value: facture_class, name: '@class'}),
          DOM.input({value: facture_id,    name: 'facture_id'}),
          DOM.input({value: 'now',         name: field + 'date_printed'}),
          DOM.input({value: User.id,       name: field + 'user_printed'})
        );
        onSubmitFormAjax(updateForm , this.callbackModif.bind(this).curry(form));
      }
      return;
    }
    setTimeout(this.checkDocClose.bind(this).curry(url, form, facture_class, facture_id, type_pdf), 250);
  },

  editEvt: function(evenement_guid) {
    var url = new Url("facturation", "vw_edit_facture_evt");
    url.addParam("evenement_guid", evenement_guid);
    url.requestModal('90%', '90%');
  },

  reloadEvt: function(evenement_guid, reload_acts, callback) {
    if (!evenement_guid) {
      evenement_guid = Facture.evenement_guid;
    }
    var url = new Url("facturation", "vw_facture_evt");
    url.addParam("evenement_guid", evenement_guid);
    url.requestUpdate('reglement_evt', callback);

    // Rafraichissement des actes CCAM et NGAP
    if (reload_acts && Preferences.ccam_consultation == "1" && (Preferences.MODCONSULT == "1" || Preferences.UISTYLE == "tamm")){
      ActesCCAM.refreshList(Facture.evenement_id, Facture.user_id);
      if (window.ActesNGAP) {
        ActesNGAP.refreshList();
      }
      if ($('fraisdivers')) {
        refreshFraisDivers();
      }

      if (!window.ActesNGAP && $('Actes')) {
        loadActes();
      }
    }
  },

  submitEvt: function(form, evenement_guid, reload_acts, callback) {
    onSubmitFormAjax(form, {
      onComplete: function () {
        Facture.reloadEvt(evenement_guid, reload_acts, callback);
      }
    }
    );
  },

  updateEtatSearch: function() {
    var form = getForm("choice-facture");
    if ($V(form.type_date_search) == "cloture") {
      form.search_easy[2].disabled = "disabled";
    }
    else {
      form.search_easy[2].disabled = "";
    }
  },
  viewPatient: function() {
    var form = getForm("choice-facture");
    if (form.patient_id.value) {
      var url = new Url('patients', 'vw_edit_patients', 'tab');
      url.addElement(form.patient_id);
      url.redirect();
    }
  },
  changePage: function(page) {
    var form = getForm("choice-facture");
    var url = new Url("facturation" , "ajax_list_factures");
    url.addParam('facture_class', $V(form.facture_class));
    url.addParam('page'         , page);
    url.requestUpdate("liste_factures");
  },
  showLegend: function(facture_class) {
    var url = new Url('facturation', 'vw_legende');
    url.addParam('classe', facture_class);
    url.requestModal(200);
  },
  refreshList: function(print){
    var form = getForm("choice-facture");
    if(!$V(form._pat_name)){
      form.patient_id.value = '';
    }
    var url = new Url("facturation" , "ajax_list_factures");
    url.addFormData(form);
    url.addParam('search_easy[]', $V(form.search_easy), true);
    if (print) {
      url.addParam("print" , 1);
      url.popup();
    }
    else {
      url.requestUpdate("liste_factures");
    }
  },
  gestionFacture: function (sejour_id) {
    var url = new Url('facturation', 'vw_factures_sejour');
    url.addParam('sejour_id', sejour_id);
    url.requestModal();
  },
  refreshAssurance: function(facture_guid) {
    var url = new Url('facturation', 'ajax_list_assurances');
    url.addParam('facture_guid', facture_guid);
    url.requestUpdate('refresh-assurance');
  },
  saveAssurance: function(form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        Facture.refreshAssurance($V(form.facture_guid))
      }
    });
  },
  reloadFactureModal: function(facture_id, facture_class, id_reload){
    var url = new Url('facturation', 'ajax_view_facture');
    url.addParam('object_class'  , facture_class);
    url.addParam('facture_id'    , facture_id);
    var facture = $('reload-'+facture_class+'-'+facture_id);
    if (facture) {
      url.requestUpdate('reload-'+facture_class+'-'+facture_id);
    }
    else if (id_reload) {
      url.requestUpdate(id_reload);
    }
  },
  editRepartition: function(facture_id, facture_class){
    var url = new Url("facturation", "ajax_edit_repartition");
    url.addParam("facture_id"   , facture_id);
    url.addParam("facture_class", facture_class);
    url.requestModal();
  },
  editDateFacture: function(form){
    $V(form.cloture, $V(form.ouverture));
    return onSubmitFormAjax(form);
  },
  filterFullName: function(input) {
    table = input.up("table");
    table.select("tr").invoke("show");

    var term = $V(input);
    if (!term) {
      return;
    }

    var view = "._assurance_patient_view";

    table.select(view).each(function (e) {
      if (!e.innerHTML.like(term)) {
        var line = e.up('tr');
        line.hide();
        line.next().hide();
      }
    });
  },
  printFactureFR: function(facture_id, facture_class){
    var url = new Url("facturation", "print_facture");
    url.addParam("facture_id"   , facture_id);
    url.addParam("facture_class", facture_class);
    url.addParam('suppressHeaders', '1');
    url.pop();
  },

  sendFactureByMail: function (facture_id, facture_class) {
    new Url("facturation", "viewSendFactureByMail")
      .addParam("facture_id"   , facture_id)
      .addParam("facture_class", facture_class)
      .requestModal();
  },

  showFiles: function (patient_id, facture_guid) {
    var url = new Url('patients', 'vw_all_docs');
    url.addParam("patient_id", patient_id);
    url.addParam('context_guid', facture_guid);
    url.requestUpdate('files_facture-'+facture_guid);
  },

  togglePratSelector: function (form) {
    form.activeChirSel.toggle();
    form.allChirSel.toggle();
    $V(form.chirSel, form.allChirSel.getStyle('display') !== 'none' ? $V(form.allChirSel) : $V(form.activeChirSel));
  },

  addKeyUpListener: function() {
    var form = getForm('choice-facture');
    form.num_facture.on('keyup', function (e) {
      if (e.key === "Enter") {
        $V(form.page, 0);
        Facture.refreshList();
      }
    });
  },

  viewTotaux: function() {
    var oForm = getForm("printFrm");
    var url = new Url("facturation", "ajax_total_cotation");
    url.addParam("chir_id", $V(oForm.chir));
    url.addParam('date_min', $V(oForm._date_min));
    url.addParam('date_max', $V(oForm._date_max));
    url.popup(1000, 600);
  },
  TdbCotation: {
    selectedLines: [],
    currentPage: 0,
    refreshList: function(form, page) {
      this.currentPage = typeof(page) !== 'undefined' ? page : this.currentPage;
      this.selectedLines = [];
      var url = new Url('facturation', 'vw_tdb_cotation')
        .addNotNullParam('page', typeof(page) !== 'undefined' ? page : this.currentPage)
        .addNotNullParam('get_consults', 1);
      if (form) {
        url.addFormData(form)
          .addParam('use_disabled_praticien', $V(form.use_disabled_praticien) ? 1 : 0)
          .addParam('praticien_id', $V(form.chirSel));
      }
      url.requestUpdate('consultations_list');
    },
    allCheck: function(input) {
      $('consultations_list').select('.tdb-cotation-check').each(
        function(e) {
          e.checked = input.checked;
          this.checkLine(e, false);
        }.bind(this)
      );
      this.controlCount();
    },
    checkLine: function(input, controlAll) {
      if (input.checked) {
        this.selectedLines.push(input.get('consultation-id'));
      }
      else {
        var oldLines = JSON.parse(JSON.stringify(this.selectedLines));
        this.selectedLines = [];
        oldLines.each(
          function(e) {
            if (e === input.get('consultation-id')) {
              return;
            }
            this.selectedLines.push(e);
          }.bind(this)
        );
      }
      if (typeof(controlAll) === 'undefined' || controlAll) {
        this.controlAllCheck();
        this.controlCount();
      }
    },
    controlAllCheck: function() {
      var toCheck = true;
      $('consultations_list').select('.tdb-cotation-check').each(
        function(e) {
          if (!toCheck || e.checked) {
            return;
          }
          toCheck = false;
        }
      );
      $('tdb_cotation_all_check').checked = toCheck;
    },
    controlCount: function() {
      $('tdb_cotation_multiple_cloture_button')
        .update($T('CConsultation-action-close-cotation') + ' (' + this.selectedLines.length + ')')
        .disabled = this.selectedLines.length === 0;
    },
    clotureCotation: function() {
      if (!confirm($T('CConsultation-action-close-cotation') + ' (' + this.selectedLines.length + ') ?')) {
        return;
      }
      new Url('facturation', 'ajax_tdb_cotation_multiple_cloture')
        .addParam('consultation_ids[]', this.selectedLines, true)
        .requestUpdate(
          'tdb_cotation_multiple_cloture',
          function() {
            this.refreshList();
          }.bind(this)
        );
    }
  },
};
