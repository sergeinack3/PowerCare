/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

flipChambre = function (chambre_id) {
  Element.classNames('chambre-' + chambre_id).flip('chambrecollapse', 'chambreexpand');
  $("chambre-" + chambre_id).down('tbody').toggleClassName('opened');
};

flipSejour = function (sejour_id) {
  Element.classNames("sejour_" + sejour_id).flip("sejourcollapse", "sejourexpand");
  $('sejour_' + sejour_id).down('tbody').toggleClassName('opened');
};

flipAffectationCouloir = function (affectation_id) {
  Element.classNames('affectation_' + affectation_id).flip('sejourcollapse', 'sejourexpand');
  $('affectation_' + affectation_id).down('tbody').toggleClassName('opened');
};

var selected_hospitalisation = null;
var selected_hospi = false;
selectHospitalisation = function (sejour_id) {
  var element = $("hospitalisation" + selected_hospitalisation);
  if (element) {
    element.checked = false;
  }
  selected_hospitalisation = sejour_id;
  selected_hospi = true;
  submitAffectation();
};

var selected_lit = null;
selectLit = function (lit_id) {
  var element = $("lit" + selected_lit);
  if (element) {
    element.checked = false;
  }
  selected_lit = lit_id;
  submitAffectation();
};

var selected_affectation = null;
selectAffectation = function (affectation_id) {
  var element = $("affectation" + selected_affectation);

  if (element) {
    element.checked = false;
  }
  selected_affectation = affectation_id;
  submitAffectation();
};

submitAffectation = function () {
  if (selected_lit) {
    // Séjour
    if (selected_hospi) {
      var oForm = selected_hospitalisation ?
        getForm("addAffectationsejour_" + selected_hospitalisation) :
        getForm("addAffectationsejour");
      oForm.lit_id.value = selected_lit;

      return checkSubmit(oForm);
    }
    // Affectation dans un couloir
    else if (selected_affectation) {
      var form = getForm("addAffectationaffectation_" + selected_affectation);
      $V(form.lit_id, selected_lit);
      return checkSubmit(form);
    }
  }
  return false;
};

Droppables.addLit = function (lit_id) {
  Droppables.add("lit-" + lit_id, {
    onDrop:     function (element) {
      DragDropSejour(element.id, lit_id);
    },
    hoverclass: "dropover"
  });
};

DragDropSejour = function (sejour_id, lit_id) {
  if (sejour_id == "sejour_bloque") {
    sejour_id = "sejour";
  }
  var oForm = getForm("addAffectation" + sejour_id);
  oForm.lit_id.value = lit_id;
  return checkSubmit(oForm);
};

submitAffectationSplit = function (oForm) {
  oForm._new_lit_id.value = selected_lit;
  if (!selected_lit) {
    alert("Veuillez sélectionner un nouveau lit et revalider la date");
    return false;
  }

  if (oForm._date_split.value <= oForm.entree.value ||
    oForm._date_split.value >= oForm.sortie.value) {
    var msg = "La date de déplacement (" + oForm._date_split.value + ") doit être comprise entre";
    msg += "\n- la date d'entrée: " + oForm.entree.value;
    msg += "\n- la date de sortie: " + oForm.sortie.value;
    alert(msg);
    return false;
  }

  oForm.insert(DOM.input({name: "callback", type: "hidden", value: "reloadTableau"}));

  return onSubmitFormAjax(oForm);
};

checkSubmit = function (form) {
  new Url("hospi", "ajax_check_placement", "raw")
    .addFormData(form)
    .requestJSON(function (result) {
      if ((!result.patient_mineur || confirm($T("warning-patient_mineur_majeur"))) &&
        (!result.sexe_opposes || confirm($T("warning-sexe_opposes")))) {
        var affectation_id = $V(form.affectation_id);
        var sejour_id = $V(form.sejour_id);

        // On masque le patient non placé dans le couloir
        if (affectation_id) {
          $("affectation_" + affectation_id).hide();
        }
        // Ou on masque le patient non placé
        else if (sejour_id) {
          $("sejour_" + sejour_id).hide();
        }

        form.insert(DOM.input({name: "callback", type: "hidden", value: "reloadTableau"}));

        return onSubmitFormAjax(form);
      }

      removeSelected();

      return false;
    });
};

Calendar.setupAffectation = function (affectation_id, options) {
  options = Object.extend({
    currAffect:  {
      start: null,
      stop:  null
    },
    outerAffect: {
      start: null,
      stop:  null
    }
  }, options);

  var dates = {
    limit: {// Entrée affectation
      start: options.outerAffect.start,
      stop:  options.currAffect.stop
    }
  };

  var form;

  if (form = getForm("entreeAffectation" + affectation_id)) {
    Calendar.regField(
      form.entree,
      dates,
      {
        noView: true,
        icon: "agenda"
      }
    );
  }

  // Sortie affectation
  dates.limit = {
    start: options.currAffect.start,
    stop:  options.outerAffect.stop
  };

  if (form = getForm("sortieAffectation" + affectation_id)) {
    Calendar.regField(
      form.sortie,
      dates,
      {
        noView: true,
        icon: "agenda"
      }
    );
  }

  // Déplacement affectation
  dates.limit = {
    start: options.currAffect.start,
    stop:  options.currAffect.stop
  };

  if (form = getForm("splitAffectation" + affectation_id)) {
    Calendar.regField(form._date_split, dates, {noView: true, icon: "play" });
    form.select('i.inputExtension')[0].title = "Créer un mouvement";
  }
};

popPlanning = function () {
  new Url("hospi", "vw_affectations")
    .popup(700, 550, "Planning");
};

showRapport = function (date) {
  new Url("hospi", "vw_rapport")
    .addParam("date", date)
    .popup(800, 600, "Rapport");
};

showAlerte = function (sType_admission) {
  new Url("hospi", "vw_etat_semaine")
    .addParam("type_admission", sType_admission)
    .popup(500, 250, "Alerte");
};

toggleService = function (trigger, mode) {
  var cookie = new CookieJar(),
    service_id = trigger.value,
    container_id = "service" + service_id;

  if (trigger.checked) {
    new Url("hospi", "httpreq_vw_aff_service")
      .addParam("service_id", service_id)
      .addParam("mode", mode)
      .requestUpdate(container_id);
  }

  $(container_id).setVisible(trigger.checked);
  cookie.setValue("fullService", container_id, trigger.checked);
};

ObjectTooltip.modes.timeHospi = {
  module: "planningOp",
  action: "httpreq_get_hospi_time",
  sClass: "tooltip"
};

ObjectTooltip.createTimeHospi = function (element, chir_id, codes) {
  ObjectTooltip.createEx(element, null, "timeHospi", {
    chir_id:    chir_id,
    codes:      codes,
    javascript: 0
  });
};

printTableau = function () {
  var oForm = getForm("chgAff");
  new Url("hospi", "print_tableau")
    .addParam("date", $V(oForm.date))
    .addParam("mode", $V(oForm.mode))
    .popup(850, 600, "printAffService");
};

removeSelected = function () {
  if (selected_hospitalisation) {
    $("hospitalisation" + selected_hospitalisation).checked = false;
    selected_hospitalisation = null;
  }
  if (selected_lit) {
    $("lit" + selected_lit).checked = false;
    selected_lit = null;
  }

  selected_hospi = false;
  selected_affectation = null;
};

resetSelected = function () {
  $("hospitalisation").checked = false;
  if (selected_hospi && selected_hospitalisation) {
    $("hospitalisation" + selected_hospitalisation).checked = false;
  }
  if (selected_hospi && selected_lit && selected_hospitalisation) {
    $("sejour_" + selected_hospitalisation).remove();
    selected_hospitalisation = null;
    selected_hospi = false;
  }
  if (selected_affectation) {
    var div_affectation = $("affectation_" + selected_affectation);

    $$(".affectation_parent_" + selected_affectation).each(function (_div) {
      _div.up("tr").remove();
    });

    var parent_affectation_id;
    if (parent_affectation_id = div_affectation.get("parent_affectation_id")) {
      var parent_affectation = $("affectation_" + parent_affectation_id);

      if (parent_affectation) {
        parent_affectation.up("tr").remove();
      }
    }

    div_affectation.up("tr").remove();
    selected_affectation = null;
  }
  selected_lit = null;
};

checkAskEtab = function (affectation_id) {
  new Url("hospi", "ajax_check_ask_etab")
    .addParam("affectation_id", affectation_id)
    .requestJSON(function (result) {
      if (result) {
        openModalEtab(affectation_id);
      }
    });
};

reloadTableau = function (object_id, object) {
  if (object && object_id && object.sejour_id) {
    checkAskEtab(object_id);
  }

  resetSelected();

  var oForm = getForm("chgAff");
  var form = getForm("chgFilter");
  new Url("hospi", "ajax_tableau_affectations_lits")
    .addElement(oForm.date)
    .addElement(oForm.mode)
    .addElement(form.prestation_id)
    .requestUpdate("tableauAffectations");
};
