/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlageConsultation  = window.PlageConsultation || {
  status_images : ["images/icons/status_red.png", "images/icons/status_orange.png", "images/icons/status_green.png"],
  modal: null,
  url: null,

  edit: function(plageconsult_id, debut, callback) {
    var url = new Url('cabinet', 'edit_plage_consultation');
    url.addParam('plageconsult_id', plageconsult_id);
    url.addParam('debut', debut);
    url.requestModal(800, "100%");
    this.modal = url.modalObject;
    this.url = url;
    if (callback) {
      url.modalObject.observe("afterClose", callback);
    }
  },

  print: function(plageconsult_id) {
    var url = new Url;
    url.setModuleAction("cabinet", "print_plages");
    url.addParam("plage_id", plageconsult_id);
    url.popup(700, 550, "Planning");
  },

  /**
   * Affiche les plages à imprimer
   *
   * @param {string} plagesconsult_ids Identifiants des plages à imprimer, séparés par un pipe
   */
  printPlages: function(plagesconsult_ids) {
    new Url('cabinet', 'print_plages')
      .addParam("plagesconsult_ids", plagesconsult_ids)
      .popup(700, 550, "Planning");
  },

  /**
   * Affiche la liste des consultations à imprimer
   *
   * @param {date} date - date du jour souhaité
   * @param {string} content_class - Objet concerné par le filtre
   * @param {string} content_id - ID de l'objet
   */
  printConsult: function(date, content_class, content_id) {
    var url = new Url;
    url.setModuleAction("cabinet", "print_plages");
    url.addParam('_date_min', date);
    url.addParam('_date_max', date);
    if(content_class === 'CMediusers') {
      url.addParam('chir', content_id);
    }
    if(content_class === 'CFunctions') {
      url.addParam('function_id', content_id);
    }
    url.popup(700, 550, "Planning");
  },
  
  onSubmit: function(form) {
    return onSubmitFormAjax(form, function() {
      PlageConsultation.refreshList();
      PlageConsultation.modal.close();
    });
  },
  
  checkForm: function(form, modal) {
    if (!checkForm(form)) {
      return false;
    }

    if (form.nbaffected.value!= 0 && form.nbaffected.value != "") {
      if (!(confirm("Attention, cette plage contient déjà " + form.nbaffected.value + " consultation(s).\n\nVoulez-vous appliquer les modifications?"))){
        return false;
      }
    }

    //pour le compte de = chir sel
    if ($V(form.chir_id) == $V(form.pour_compte_id)) {
      alert("Vous ne pouvez pas créer une plage pour le compte de vous-même");
      return false;
    }

    // remplacement de soit même
    if ($V(form.chir_id) == $V(form.remplacant_id)) {
      alert("Vous ne pouvez pas vous remplacer vous-même");
      return false;
    }

    if (modal) {
      return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
    }
    else {
      return true;
    }
  },
  
  resfreshImageStatus : function(element){
    if (!element.get('id')) {
      return;
    }
  
    element.title = "";
    element.src   = "style/mediboard_ext/images/icons/loading.gif";
    
    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      element.src = PlageConsultation.status_images[status.reachable];
      });
  },

  promptBackup: function() {
    var freq = Preferences.dPcabinet_offline_mode_frequency;
    if (!freq || freq == 0) {
      return;
    }

    var latestBackup = store.get("cabinet-backup");
    var downloadDate = null;

    if (!latestBackup || !latestBackup[User.id] || latestBackup[User.id].ask + freq*3600000 < Date.now()) {
      downloadDate = (latestBackup && latestBackup[User.id] && latestBackup[User.id].download) || null;

      var date = (new Date(downloadDate)).toLocaleDateTime();
      var msg  = $T("dPcabinet-msg-Do you want to make a backup?") + "\n";

      if (downloadDate) {
        msg += $T("dPcabinet-msg-Latest one was at %s", date);
      }
      else {
        msg += $T("dPcabinet-msg-You never made a backup from this browser");
      }

      if (confirm(msg)) {
        PlageConsultation.downloadBackup();
        return;
      }
    }

    latestBackup = latestBackup || {};
    latestBackup[User.id] = {
      download: downloadDate,
      ask:      Date.now()
    };

    store.set("cabinet-backup", latestBackup);
  },
  downloadBackup: function() {
    var url = new Url("cabinet", "download_backup");
    url.addParam("_aio", 1);
    url.addParam("function_id", User.function.id);
    url.pop(500, 300, "Sauvegarde cabinet");

    var latestBackup = store.get("cabinet-backup") || {};

    latestBackup[User.id] = {
      download: Date.now(),
      ask:      Date.now()
    };

    store.set("cabinet-backup", latestBackup);
  },
  showPratsByFunction: function(function_id, all_prat) {
    var url = new Url("cabinet", "ajax_show_prats_by_function");
    url.addParam("function_id"   , function_id);
    url.addParam("prats_selected", all_prat);
    url.requestUpdate("filter_prats");
  }
};

PlageConsultation.promptBackup();

CreneauConsultation = {
  /**
   * Open the modal to show the next available time slot
   *
   * @param chir_id
   * @param function_id
   * @param prise_rdv
   * @param only_func
   * @param rdv
   * @param date
   */
  modalPriseRDVTimeSlot: function (chir_id, function_id, prise_rdv, only_func, rdv, date) {
    // no chir, no function
    if (!chir_id && !function_id) {
      if (!alert($T("CPlageconsult-msg-You have not selected a practitioner or medical office"))) {
        return;
      }
    }
    var url =new Url("dPcabinet", "vw_next_slots")
      .addParam("prat_id", chir_id)
      .addParam("function_id", function_id)
      .addParam("prise_rdv", prise_rdv)
      .addParam("only_func", only_func)
      .addParam("rdv", rdv)
      .addParam("date", date)
      .requestModal("100%", "100%");
  },

  /**
   * Select All the lines
   */
  selectAllLines: function (classname, valeur) {
    $$('input.' + classname).each(function (elt) {
      elt.checked = valeur;
    });
  },

  /**
   * Time sleep
   */
  timeSleep: function () {
    var waitUntil = new Date().getTime() + 3 * 1000;
    while (new Date().getTime() < waitUntil) {
      true;
    }
  },

  /**
   * Remove the loading message
   */
  removeLoadingMessage: function () {
    //supprimer message de chargement
    if ($("table_time_slot")) {
      $("table_time_slot").down('tr.tr_loading').remove();
    }
  },
  /**
   * Show list of the next slots
   *
   * @param function_id
   * @param week_number
   * @param rdv
   * @param year
   */
  showListNextSlots: function (function_id, week_number, rdv, year) {
    var oForm = getForm('selectNextSlots');

    // sélectionner plusieurs praticiens
    var get_prats_ids = $V(oForm.select("input.praticiens:checked"));
    var prats_ids = $A(get_prats_ids).join(',');

    // sélectionner plusieurs jours
    var get_days = $V(oForm.select("input.weekday:checked"));
    var days = $A(get_days).join(',');

    // sélectionner plusieurs heures
    var get_times = $V(oForm.select("input.timeday:checked"));
    var times = $A(get_times).join(',');

    // Récupère le libelle de la plage
    var libelle_plage = $V(oForm.plage_libelle);

    new Url("cabinet", "vw_list_next_slots")
      .addParam("week_number", week_number)
      .addParam("year", year)
      .addParam("prats_ids", prats_ids)
      .addParam("days", days)
      .addParam("times", times)
      .addParam("libelle_plage", libelle_plage)
      .addParam("rdv", rdv)
      .requestUpdate("table_time_slot");
  },
  /**
   * Save a preference and refresh
   *
   * @param start_week_number
   * @returns {Boolean}
   */
  savePrefSlotAndReload: function (start_week_number) {
    var form = getForm("editPrefFreeSlot");
    $V(form.elements["pref[search_free_slot]"], start_week_number);
    return onSubmitFormAjax(form, function () {
      Control.Modal.refresh();
    });
  },
  /**
   * Open the modal rdv
   *
   * @param consult_id
   * @param date
   * @param heure
   * @param plage_id
   */
  modalPriseRDV: function (consult_id, date, heure, plage_id) {
    var url = new Url("dPcabinet", "edit_planning");
    url.addParam("dialog", 1);
    url.addParam("consultation_id", consult_id);
    url.addParam("date_planning", date);
    url.addParam("heure", heure);
    url.addParam("plageconsult_id", plage_id);
    url.modal({width: "100%", height: "100%", afterClose: window.refreshPlanning});
  },
  /**
   * Get datas for a consultation
   *
   * @param date
   * @param date2
   * @param heure
   * @param plage_id
   */
  getDataForConsult: function (date, date2, heure, plage_id) {
    $V(getForm('editFrm').heure, heure);
    $V(getForm('editFrm')._date_planning, date);
    $V(getForm('editFrm')._date, date2);
    $V(getForm('editFrm').plageconsult_id, plage_id);
    Control.Modal.close();
  }
};
