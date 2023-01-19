/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

updateTokenCim10 = function() {
  onSubmitFormAjax(getForm("editDiagFrm"), DossierMedical.reloadDossierPatient);
};

updateTokenCim10Anesth = function(){
  onSubmitFormAjax(getForm("editDiagAnesthFrm"), DossierMedical.reloadDossierSejour);
};

onSubmitAnt = function (form, type_see) {
  var rques = $(form.rques);
  if (!rques.present()) {
    return false;
  }

  onSubmitFormAjax(form, function() {
    if (type_see) {
      DossierMedical.reloadDossierPatient(null, type_see);
    }
    else {
      DossierMedical.reloadDossiersMedicaux();
    }
    if (window.reloadAtcd) {
      reloadAtcd();
    }
    if (window.reloadAtcdMajeur) {
      reloadAtcdMajeur();
    }
    if (window.reloadAtcdOp) {
      reloadAtcdOp();
    }

    var div_links = form.down("div.hypertext_links_area");
    if (div_links) {
      div_links.update();
      div_links.up("fieldset").hide();
    }
  });

  // Après l'ajout d'antécédents
  if (Preferences.empty_form_atcd == "1") {
    $V(form.date    , "");
    $V(form.date_da , "");
    $V(form.type    , "");
    $V(form.appareil, "");
  }

  if (form.__majeur) {
    form.__majeur.checked = false;
    $V(form.majeur, 0);
  }
  if (form.__important) {
    form.__important.checked = false;
    $V(form.important, 0);
  }

  $V(form.keywords_composant, "");
  $V(form.cds, "");

  $V(form._idex_code, "");
  $V(form._idex_tag, "");

  rques.clear().focus();

  return false;
};

DossierMedical = {
  sejour_id         : null,
  patient_id        : null,
  dossier_anesth_id : null,
  _is_anesth        : null,
  reload_dbl        : false,
  sort_by_date      : (Preferences.sort_atc_by_date == '1') ? 1 : 0,
  show_gestion_tp   : true,
  context_date_min  : null,
  context_date_max  : null,
  ant_tab           : null,

  updateSejourId : function(sejour_id) {
    this.sejour_id = sejour_id;

    // Mise à jour des formulaire
    if (document.editTrmtFrm) {
      $V(document.editTrmtFrm._sejour_id, sejour_id, false);
    }
    if (document.editAntFrm) {
      $V(document.editAntFrm._sejour_id, sejour_id, false);
    }
  },

  reloadDossierPatient: function(id_div, type_see) {
    if (type_see === "traitement" && !id_div) {
      id_div = "list_traitements";
    }
    id_div = id_div && Object.isString(id_div) ? id_div : ("listAnt" + DossierMedical.sejour_id);
    type_see = type_see ? type_see : '';
    var antUrl = new Url("cabinet", "httpreq_vw_list_antecedents");
    antUrl.addParam("patient_id"  , DossierMedical.patient_id);
    antUrl.addParam("_is_anesth"  , DossierMedical._is_anesth);
    antUrl.addParam("sort_by_date", DossierMedical.sort_by_date);
    antUrl.addParam("dossier_anesth_id", DossierMedical.dossier_anesth_id);
    antUrl.addParam("type_see", type_see);
    antUrl.addParam("show_gestion_tp", DossierMedical.show_gestion_tp ? 1 : 0);
    if (DossierMedical._is_anesth) {
      antUrl.addParam("sejour_id", DossierMedical.sejour_id);
    }

    if (DossierMedical.context_date_max) {
      antUrl.addParam("context_date_max", DossierMedical.context_date_max);
    }

    if (DossierMedical.context_date_min) {
      antUrl.addParam("context_date_min", DossierMedical.context_date_min);
    }

    if (DossierMedical.reload_dbl) {
      EchelleTri.refreshAntecedentsPatient();
    }
    antUrl.requestUpdate(id_div);
  },

  toggleSortAntecedent: function(type_see) {
    DossierMedical.sort_by_date = DossierMedical.sort_by_date ? 0 : 1;
    DossierMedical.reloadDossierPatient(null, type_see);
  },

  reloadDossierSejour: function() {
    if ($("listAntCAnesth" + DossierMedical.sejour_id)) {
      new Url("cabinet", "httpreq_vw_list_antecedents_anesth")
        .addParam("sejour_id", DossierMedical.sejour_id)
        .requestUpdate("listAntCAnesth" + DossierMedical.sejour_id);
    }
  },

  reloadDossiersMedicaux: function() {
    DossierMedical.reloadDossierPatient();
    if (DossierMedical._is_anesth || DossierMedical.sejour_id) {
      DossierMedical.reloadDossierSejour();
    }
  },

  submitHideAtcd: function(form) {
    return onSubmitFormAjax(form, function() {
      DossierMedical.reloadDossierPatient();
    });
  }
};
