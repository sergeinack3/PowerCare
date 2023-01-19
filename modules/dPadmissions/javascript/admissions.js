/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Admissions = {
  totalUpdater:         null,
  listUpdater:          null,
  target_date:          null,
  pre_admission_filter: null,
  pre_admission_sejour_prepared: null,
  pre_admission_type_pec: null,
  pre_admission_period: null,
  table_id:             null,
  required_dest_when_transfer:  null,
  required_dest_when_mutation:  null,
  etab_externe_transfert_obligatory:  null,
  transport_sortie_mandatory: null,

  filter: function (input, table, type) {
    var type_search = type || "CPatient-view";
    table = $(table);
    table.select("tr").invoke("show");

    var term = $V(input);
    if (!term) {
      return;
    }

    table.select("."+type_search).each(function (e) {
      if (!e.innerHTML.like(term)) {

        // Preadmissions: tr are added when there are several M.D.s
        $$('.more[consult_id="'+e.up('tr').dataset.id+'"').each(function (e) {
          e.hide();
        });
        e.up("tr").hide();
      }
    });
  },

  togglePrint: function (status) {
    var table = $(Admissions.table_id);
    table.select("input[name=print_doc]").each(function (elt) {
      elt.checked = status ? "checked" : "";
    });
  },

  printDHE: function (type, object_id) {
    var url = new Url("planningOp", "view_planning");
    url.addParam(type, object_id);
    url.popup(700, 550, "DHE");
  },

  choosePrintForSelection: function () {
    Admissions.beforePrint();
    Modal.open('area_prompt_modele', {width: '600px', height: '300px'});
  },

  printForSelection: function (modele_id) {
    if (!modele_id) {
      alert("Veuillez choisir un modèle avant de lancer l'impression");
      return false;
    }
    var table = $(Admissions.table_id);
    var sejours_ids = table.select("input[name=print_doc]:checked").pluck("value");

    if (sejours_ids == "") {
      alert("Veuillez sélectionner au minimum un patient pour l'impression");
      return false;
    }

    var oForm = getForm("chooseDoc");
    $V(oForm.sejours_ids, sejours_ids.join(","));
    oForm.submit();
    return true;
  },

  rememberSelection: function () {
    var table = $(Admissions.table_id);
    window.sejours_ids = table.select("input[name=print_doc]:checked").pluck("value");
  },

  restoreSelection: function () {
    var table = $(Admissions.table_id);

    table.select("input[name=print_doc]").each(function (elt) {
      if ($H(window.sejours_ids).index(elt.value)) {
        elt.checked = true;
      }
    });
  },

  printFichesAnesth: function () {
    var url = new Url("admissions", "printFichesAnesth", 'raw');
    var table = $(Admissions.table_id);
    var sejours_ids = table.select("input[name=print_doc]:checked").pluck("value");

    if (sejours_ids == "") {
      alert("Veuillez sélectionner au minimum un patient pour l'impression");
      return false;
    }

    url.popup(700, 500, "fiches_anesth", null, {sejours_ids: sejours_ids.join(",")});
  },

  printPlanSoins: function () {
    var table = $(Admissions.table_id);
    var sejours_ids = table.select("input[name=print_doc]:checked").pluck("value");

    if (sejours_ids == "") {
      alert("Veuillez sélectionner au minimum un patient pour l'impression");
      return false;
    }

    new Url("soins", "offline_plan_soins")
      .addParam("sejours_ids", sejours_ids.join(","))
      .addParam("mode_dupa", 1)
      .popup(700, 500);
  },

  chooseEtiquette: function () {
    var url = new Url("hospi", "ajax_choose_modele_etiquette");
    url.addParam("object_class", "CSejour");
    url.addParam("custom_function", "Admissions.printEtiquettes");
    url.requestModal("40%", "40%");
  },

  printEtiquettes: function (object_class, object_id, modele_etiquette_id) {
    var table = $(Admissions.table_id);
    var sejours_ids = table.select("input[name=print_doc]:checked").pluck("value");

    if (sejours_ids == "") {
      alert("Veuillez sélectionner au minimum un patient pour l'impression");
      return false;
    }

    var form = getForm("download_etiqs");
    $V(form.modele_etiquette_id, modele_etiquette_id);
    $V(form.sejours_ids, sejours_ids.join("-"));
    form.submit();
    Control.Modal.close();
  },

  beforePrint: function () {
    if (Admissions.totalUpdater) {
      Admissions.totalUpdater.stop();
      Admissions.listUpdater.stop();
    }
  },

  afterPrint: function () {
    Control.Modal.close();
    if (Admissions.totalUpdater) {
      Admissions.totalUpdater.resume();
      Admissions.listUpdater.resume();
    }
  },

  toggleMultipleServices: function (elt) {
    var status = elt.checked;
    var form = elt.form;
    var elt_service_id = form.service_id;
    elt_service_id.multiple = status;
    elt_service_id.size = status ? 5 : 1;
  },

  showLegend: function () {
    new Url("admissions", "vw_legende").requestModal();
  },

  showDocs: function (sejour_id) {
    if (Admissions.totalUpdater) {
      Admissions.totalUpdater.stop();
      Admissions.listUpdater.stop();
    }
    var url = new Url("hospi", "httpreq_documents_sejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("with_patient", 1);
    url.requestModal('80%', '90%');
    url.modalObject.observe("afterClose", function () {
      if (Admissions.totalUpdater) {
        Admissions.totalUpdater.resume();
        Admissions.listUpdater.resume();
      }
    });
  },

  updateSummaryPreAdmissions: function (sdate) {
    if (sdate) {
      this.target_date = sdate;
    }
    var admUrl = new Url("admissions", "httpreq_vw_all_preadmissions");
    admUrl.addParam("date", this.target_date);
    admUrl.requestUpdate('allPreAdmissions');
  },

  updatePeriodicalSummaryPreAdmissions: function (frequency) {
    setInterval(function () {
      Admissions.updateSummaryPreAdmissions();
    }, frequency * 1000);
  },

  updateListPreAdmissions: function (sdate) {
    var admUrl = new Url("admissions", "httpreq_vw_preadmissions");
    if (sdate) {
      this.target_date = sdate;
      admUrl.addParam("date", this.target_date);
    }
    admUrl.addParam("filter", this.pre_admission_filter);
    admUrl.addParam("sejour_prepared", this.pre_admission_sejour_prepared);
    admUrl.addNotNullParam("type_pec[]", this.pre_admission_type_pec, true);
    admUrl.addParam("period", this.pre_admission_period);
    admUrl.requestUpdate('listPreAdmissions');

    //update du selecteur
    var lines = $("allPreAdmissions").select('table tbody tr.preAdmission-day').invoke("removeClassName", "selected");
    var target_td = $('paday_' + this.target_date);
    if (target_td) {
      target_td.addClassName("selected");
    }
  },

  updatePeriodicalPreAdmissions: function (frequency) {
    setInterval(function () {
      Admissions.updateListPreAdmissions();
    }, frequency * 1000);
  },

  validerEntree: function (sejour_id, callback, callback_close) {
    new Url("admissions", "ajax_edit_entree")
      .addParam("sejour_id", sejour_id)
      .addParam("module", App.m)
      .requestModal("725px", "550px")
      .modalObject.observe("afterClose", callback_close);
    document.stopObserving("mb:valider_entree");
    document.observe("mb:valider_entree", callback);
  },

  validerSortie: function (sejour_id, modify_sortie_prevue, callback, group_id) {
    new Url("admissions", "ajax_edit_sortie")
      .addParam("sejour_id", sejour_id)
      .addParam("module", App.m)
      .addNotNullParam("g", group_id)
      .addParam("modify_sortie_prevue", modify_sortie_prevue ? 1 : 0)
      .requestModal("725px", "535px", {onClose: callback});
  },

  changeSortie: function (form, sejour_id) {
    var mode_sortie = $V(form.mode_sortie);

    //Affichage des champs complémentaires en fonction du mode de sortie
    $('sortie_transfert_' + sejour_id).setVisible(mode_sortie == "transfert" || mode_sortie == "transfert_acte");
    $('sortie_service_mutation_' + sejour_id).setVisible(mode_sortie == "mutation");
    $('lit_sortie_mutation_' + sejour_id).setVisible(mode_sortie === "mutation");

    var sortie_deces = $('sortie_deces_' + sejour_id);
    sortie_deces.setVisible(mode_sortie === "deces");

    var transport_sortie_mutation;
    if (transport_sortie_mutation = $('transport_sortie_mutation_' + sejour_id)) {
      var visible = mode_sortie != 'mutation';
      transport_sortie_mutation.setVisible(visible);

      if (Admissions.transport_sortie_mandatory) {
        var label = transport_sortie_mutation.down('label');
        var transport_sortie = form.transport_sortie;
        label[visible ? 'addClassName' : 'removeClassName']($V(transport_sortie) ? 'notNullOK' : 'notNull');
        transport_sortie[visible ? 'addClassName' : 'removeClassName']('notNull');
      }
    }
    //Suppression des valeurs lors du changement de mode de sortie
    if (mode_sortie != "mutation") {
      $V(form.service_sortie_id, "");
      $V(form.service_sortie_id_autocomplete_view, "");
    }

    if (mode_sortie != "transfert" && mode_sortie != "transfert_acte") {
      $V(form.etablissement_sortie_id, "");
      $V(form.etablissement_sortie_id_autocomplete_view, "");
    }

    if ((Admissions.required_dest_when_mutation == 1 && mode_sortie == "mutation") ||
      (Admissions.required_dest_when_transfer == 1 && (mode_sortie == "transfert" || mode_sortie == "transfert_acte"))) {
      form.destination.addClassName('notNull');
      form.destination.getLabel().addClassName('notNull');
      form.destination.observe("change", notNullOK)
        .observe("keyup", notNullOK)
        .observe("ui:change", notNullOK);
    }
    else if ($V(form.elements.required_destination) == 0 &&
      (Admissions.required_dest_when_transfer == 1 || Admissions.required_dest_when_mutation == 1)) {
      form.destination.removeClassName('notNull');
      form.destination.getLabel().removeClassName('notNull')
        .removeClassName('notNullOK');
    }
    if (Admissions.etab_externe_transfert_obligatory == 1) {
      var etab_externe = $(form.etablissement_sortie_id);
      var class_etablissement_sortie = $V(form.etablissement_sortie_id) ? "notNullOK" : "notNull";
      if (mode_sortie == "transfert" || mode_sortie == "transfert_acte") {
        etab_externe.addClassName(class_etablissement_sortie);
        $('labelFor_'+form.name+'_etablissement_sortie_id').addClassName(class_etablissement_sortie);
      }
      else {
        etab_externe.removeClassName(class_etablissement_sortie);
        $('labelFor_' + form.name + '_etablissement_sortie_id').removeClassName(class_etablissement_sortie);
      }
    }

    if (mode_sortie != "deces") {
      $V(form._date_deces, "");
      $V(form._date_deces_da, "");
    }

    var label_deces = form._date_deces.getLabel();

    label_deces.removeClassName("notNull");
    form._date_deces.removeClassName("notNull");

    if (mode_sortie === "deces") {
      label_deces.addClassName("notNull");
      form._date_deces.addClassName("notNull");
      form._date_deces.observe("change", notNullOK)
        .observe("keyup", notNullOK)
        .observe("ui:change", notNullOK);
      if (!$V(form._date_deces)) {
        var date_deces = sortie_deces.get('date_deces');
        var date_deces_da = sortie_deces.get('date_deces_da');

        $V(form._date_deces, date_deces ? date_deces : $V(form.sortie_reelle));
        $V(form._date_deces_da, date_deces_da ? date_deces_da : $V(form.sortie_reelle_da));
      }
      else {
        label_deces.removeClassName("notNull");
        form._date_deces.removeClassName("notNull");
        label_deces.addClassName("notNullOK");
        form._date_deces.addClassName("notNullOK");
      }
    }
  },

  annulerSortie: function (form, callback) {
    if (!confirm($T('admissions-Confirm canceling the leaving of', $V(form.view_patient)))) {
      return false;
    }

    $V(form.sortie_reelle, "");
    $V(form.mode_sortie, "");
    $V(form.mode_sortie_id, "");
    form.mode_sortie.removeClassName("notNull");

    if (form.transport_sortie) {
      $V(form.transport_sortie, "");
      form.transport_sortie.removeClassName("notNull");
    }

    if (form.rques_transport_sortie) {
      $V(form.rques_transport_sortie, "");
    }

    if (form.commentaires_sortie) {
      $V(form.commentaires_sortie, "");
    }

    if (form._sejours_enfants_ids) {
      var tokenfield = new TokenField(form._sejours_enfants_ids);
      var text = "Voulez-vous effectuer dans un même temps l'annulation de la sortie de l'enfant ";
      tokenfield.getValues().each(function(element) {
        var form_enfant = getForm("validerSortieEnfant" + element);
        if (confirm(text + $V(form_enfant.view_patient))) {
          $V(form_enfant.sortie_reelle, "");
          $V(form_enfant.mode_sortie, "normal");
          $V(form_enfant.mode_sortie_id, "");
          form_enfant.onsubmit();
        }
      });
    }

    $V(form._sejours_enfants_ids, "");

    var form_mere = getForm('validerSortieMere');
    if (form_mere && form_mere != form) {
      $V(form_mere.sortie_reelle, "");
      $V(form_mere.mode_sortie, "");
      $V(form_mere.mode_sortie_id, "");
      Admissions.annulerSortie(
        form_mere,
        function() {
          if (window.reloadSortieLine) {
            reloadSortieLine($V(form_mere.sejour_id));
          }
        }
      );
    }

    /* Soumission des réglement dh sur l'intervention */
    if ($(form.name + '_reglement_dh_chir') || $(form.name + '_reglement_dh_anesth')) {
      var dh_chir = $(form.name + '_reglement_dh_chir');
      var dh_anesth = $(form.name + '_reglement_dh_anesth');
      var operation_id, form_operation;
      if (dh_chir) {
        operation_id = dh_chir.up('tr').get('operation_id');
      }
      else {
        operation_id = dh_anesth.up('tr').get('operation_id');
      }

      form_operation = getForm('editDepassementIntervSortie-' + operation_id);
      if (dh_chir) {
        $V(form_operation.elements['reglement_dh_chir'], $V(dh_chir));
        dh_chir.parentNode.removeChild(dh_chir);
      }
      if (dh_anesth) {
        $V(form_operation.elements['reglement_dh_anesth'], $V(dh_anesth));
        dh_anesth.parentNode.removeChild(dh_anesth);
      }

      form_operation.onsubmit();
    }

    return onSubmitFormAjax(form, callback);
  },

  updateLitMutation: function (form) {
    var sejour_id = $V(form.sejour_id);
    new Url('dPadmissions', 'ajax_refresh_lit')
      .addParam('sejour_id', sejour_id)
      .addParam('sortie_reelle', $V(form.sortie_reelle))
      .requestUpdate("lit_sortie_mutation_" + sejour_id);
  },

  choisirLit: function (element) {
    if (element.selectedIndex >= 0) {
      var option = element.options[element.selectedIndex];
      $V(element.form.service_sortie_id, option.get("service_id"));
      $V(element.form.service_sortie_id_autocomplete_view, option.get("name"));
    }
  },

  askconfirm: function (sejour_id) {
    Modal.open("confirmSortieModal_" + sejour_id, {
      width:  "410px",
      height: "300px"
    });
  },

  afterConfirmPassword: function (sejour_id, id_user) {
    var form_sortie = getForm("validerSortie" + sejour_id);
    var form_confirm = getForm("confirmSortie_" + sejour_id);
    //cas de la confirmation de l'autorisation de sortie
    var user_id = id_user || $V(form_confirm.user_id);
    if ($V(form_sortie.action_confirm) == 1) {
      $V(form_sortie.confirme_user_id, user_id);
      if (!$V(form_sortie.confirme)) {
        var sortie_reelle = $V(form_sortie.sortie_reelle);
        var sortie = sortie_reelle ? sortie_reelle : 'now';
        $V(form_sortie.confirme, sortie);
      }
    }
    //cas de l'annulation de l'autorisation de sortie
    else {
      $V(form_sortie.confirme, "");
      $V(form_sortie.confirme_user_id, "");
    }

    form_sortie.onsubmit();
  },

  confirmationSortie: function (form, modify_sortie_prevue, sortie_prevue, impose_lit_service_mutation, callback) {
    if (impose_lit_service_mutation && App.m == "dPurgences") {
      if (form.mode_sortie.value == "mutation" && (!form.lit_id || !form.lit_id.value) && !form.service_sortie_id.value) {
        alert($T('CRPU-_missing_lit_service_mutation'));
        return false;
      }
    }

    if (!modify_sortie_prevue && !$V(form.entree_reelle)) {
      alert($T('CSejour.no_entree_relle_for_sortie'));
      return false;
    }

    var sortie_relle = $V(form.sortie_reelle);

    if (sortie_relle) {
      sortie_relle = Date.fromDATETIME(sortie_relle);
      sortie_prevue = Date.fromDATETIME(sortie_prevue);
      if (App.m !== "dPurgences" && (sortie_relle.getDate() != sortie_prevue.getDate() || sortie_relle.getFullYear() != sortie_prevue.getFullYear())) {
        if (!confirm('La date de sortie enregistrée est différente de la date prévue, souhaitez vous confirmer la sortie du patient ?')) {
          return false;
        }
      }
    }

    if ($V(form.mode_sortie) === "deces") {
      if (!confirm('Confirmez-vous le décès de ' + $V(form.view_patient) + ' le ' + $V(form._date_deces_da) + ' ?')) {
        return false;
      }
    }

    if (form._sejours_enfants_ids) {
      var tokenfield = new TokenField(form._sejours_enfants_ids);
      tokenfield.getValues().each((function (form, modify_sortie_prevue, element) {
        var form_enfant = getForm("validerSortieEnfant" + element);
        if (!form_enfant) {
          return;
        }
        //si nous sommes en validation de sortie et que l'enfant est déjà sorti, on abandonne le traitement
        if (!modify_sortie_prevue && $V(form_enfant.sortie_reelle)) {
          return;
        }

        var action_confirm   = parseInt($V(form.action_confirm));

        var text = "Voulez-vous effectuer dans un même temps la sortie de l'enfant ";

        if (modify_sortie_prevue) {
          text = "Voulez-vous modifier dans un même temps la sortie prévue de l'enfant ";
          if (action_confirm == 1) {
            text = "Voulez-vous autoriser dans un même temps la sortie de l'enfant ";
          }
          else if (action_confirm == 0) {
            text = "Voulez-vous annuler dans un même temps l'autorisation de sortie de l'enfant ";
          }
        }

        if (confirm(text + $V(form_enfant.view_patient))) {
          if (form.mode_sortie_id) {
            $V(form_enfant.mode_sortie_id, $V(form.mode_sortie_id));
          }

          if (modify_sortie_prevue) {
            $V(form_enfant.sortie_prevue, $V(form.sortie_prevue));
            $V(form_enfant.confirme, $V(form.confirme));
            $V(form_enfant.confirme_user_id, $V(form.confirme_user_id));
          }
          else {
            $V(form_enfant.sortie_reelle, $V(form.sortie_reelle));
          }

          $V(form_enfant.mode_sortie, $V(form.mode_sortie));
          form_enfant.onsubmit();
        }
      }).curry(form, modify_sortie_prevue));
    }
    var form_mere = getForm('validerSortieMere');
    if (form_mere && form_mere != form && (modify_sortie_prevue || !$V(form_mere.sortie_reelle))) {
      if (confirm($T('admissions-Apply changes to the mother', $V(form_mere.view_patient)))) {
        if (form.mode_sortie_id) {
          $V(form_enfant.mode_sortie_id, $V(form.mode_sortie_id));
        }
        if (modify_sortie_prevue) {
          $V(form_mere.sortie_prevue, $V(form.sortie_prevue));
          $V(form_mere.confirme, $V(form.confirme));
          $V(form_mere.confirme_user_id, $V(form.confirme_user_id));
        }
        else {
          $V(form_mere.sortie_reelle, $V(form.sortie_reelle));
        }
        $V(form_mere.mode_sortie, $V(form.mode_sortie));

        Admissions.confirmationSortie(
          form_mere,
          modify_sortie_prevue,
          $V(form_mere.sortie_prevue),
          impose_lit_service_mutation,
          function() {
            if (window.reloadSortieLine) {
              reloadSortieLine($V(form_mere.sejour_id));
            }
          }
        );
      }
    }

    /* Soumission des réglement dh sur l'intervention */
    if ($(form.name + '_reglement_dh_chir') || $(form.name + '_reglement_dh_anesth')) {
      var dh_chir = $(form.name + '_reglement_dh_chir');
      var dh_anesth = $(form.name + '_reglement_dh_anesth');
      var operation_id, form_operation;
      if (dh_chir) {
        operation_id = dh_chir.up('tr').get('operation_id');
      }
      else {
        operation_id = dh_anesth.up('tr').get('operation_id');
      }

      form_operation = getForm('editDepassementIntervSortie-' + operation_id);
      if (dh_chir) {
        $V(form_operation.elements['reglement_dh_chir'], $V(dh_chir));
        dh_chir.parentNode.removeChild(dh_chir);
      }
      if (dh_anesth) {
        $V(form_operation.elements['reglement_dh_anesth'], $V(dh_anesth));
        dh_anesth.parentNode.removeChild(dh_anesth);
      }

      form_operation.onsubmit();
    }

    return onSubmitFormAjax(form, callback);
  },

  //Changement de la destination en fonction du mode sortie
  changeDestination: function (form) {
    //Contrainte à appliquer pour la destination
    var contrainteDestination = {
      "mutation":  ["", 1, 2, 3, 4, 6],
      "transfert": ["", 1, 2, 3, 4, 6],
      "transfert_acte": ["", 1, 2, 3, 4],
      "normal":    ["", 0, 7],
      "deces":     ["", 0]
    };

    if ($V(form.type) == "psy") {
      contrainteDestination.transfert_acte = ["", 0];
    }

    var destination = form.elements.destination;
    var mode_sortie = $V(form.elements.mode_sortie);

    // Aucun champ trouvé
    if (!destination) {
      return true;
    }

    //Pas de mode de sortie, activation de tous les options
    if (!mode_sortie) {
      $A(destination).each(function (option) {
        option.disabled = false
      });
      return true;
    }

    //Application des contraintes
    $A(destination).each(function (option) {
      option.disabled = !contrainteDestination[mode_sortie].include(option.value);
    });

    if (destination[destination.selectedIndex].disabled) {
      $V(destination, "");
    }

    if (!$V(destination) && $V(form.elements.required_destination) == 1 && (mode_sortie == "deces" || mode_sortie == "normal")) {
      $V(destination, "0");
    }

    return true;
  },

  //Changement de la provenance en fonction du mode d'entree
  changeProvenance: function (form) {
    //Contrainte à appliquer pour la provenance
    var contrainteProvenance = {
      0 : ["", 1, 2, 3, 4, "R"],
      6 : ["", 1, 2, 3, 4, 6],
      7 : ["", 1, 2, 3, 4, 6, "R"],
      8 : ["", 5, 7, 8],
      N : [""],
    };
    if ($V(form.type) == "psy") {
      contrainteProvenance[0] = [""];
    }

    var provenance = form.elements.provenance;
    var mode_entree = $V(form.elements.mode_entree);

    // Aucun champ trouvé
    if (!provenance) {
      return true;
    }

    //Pas de mode d'entree, activation de tous les options
    if (!mode_entree) {
      $A(provenance).each(function (option) {
        option.disabled = false
      });
      return true;
    }

    //Application des contraintes
    $A(provenance).each(function (option) {
      option.disabled = !contrainteProvenance[mode_entree].include(option.value);
    });

    if (provenance[provenance.selectedIndex].disabled) {
      $V(provenance, "");
    }

    if (!$V(provenance) && $V(form.elements.provenance) == 1 && (mode_entree == "N" || mode_entree == "8")) {
      $V(provenance, "0");
    }

    return true;
  },

  selectServices: function (view) {
    var url = new Url("hospi", "ajax_select_services");
    url.addParam("view", view);
    url.addParam("ajax_request", 0);
    url.requestModal(null, null, {maxHeight: "95%"});
  },

  selectSejours: function (view) {
    var url = new Url("admissions", "ajax_select_sejours");
    url.addParam("view", view);
    url.requestModal('300px');
  },

  printRecouvrement: function(view) {
    var form = getForm('selType');

    var url = new Url('admissions', 'print_recouvrement_dp');
    url.addParam('date', $V(form.date));
    url.addParam('type', $V(form._type_admission));
    url.addParam('services_ids', [$V(form.service_id)].flatten().join('|'));
    url.addParam('active_filter_services', $V(form.elements['active_filter_services']));
    url.addParam('prat_id', $V(form.prat_id));
    url.addParam('period', $V(form.period));
    url.addParam('type_pec[]', $V(form.elements['type_pec[]']), true);
    url.addParam('view', view);

    if ($(form.name + '_reglement_dh')) {
      url.addParam('reglement_dh', $V(form.elements['reglement_dh']));
    }

    url.popup(700, 550, 'Recouvrement des dépassements d\'honoraires');
  },

  chooseDHE: function (sejourId) {
    var url = new Url("admissions", "vw_choice_dhe");
    url.addParam("sejour_id", sejourId);
    url.requestModal("40%","20%");
  },

  printPreAdmission: function() {
    var url = new Url("admissions", "print_pre_admissions");
    url.addParam("date", this.target_date);
    url.addParam("sejour_prepared", this.pre_admission_sejour_prepared);
    url.addNotNullParam("type_pec[]", this.pre_admission_type_pec, true);
    url.addParam("period", this.pre_admission_period);
    url.addParam("filter", this.pre_admission_filter);
    url.popup(700, 550, "Pré-admissions");
  },

  printPermissions: function(date, type_externe) {
    var url = new Url("admissions", "print_permissions");
    url.addParam("date", date);
    url.popup(700, 550, "Permissions");
  },

  toggleListPresent: function() {
    $('left-column').toggle();
    ViewPort.SetAvlSize('listPresents', 1.0);
  },

  showAccueilPresentation: function(element) {
    var form = element.form;
    new Url('admissions', 'accueil_presentation')
      .addFormData(form)
      .addParam('type_pec[]', $V(form.elements['type_pec[]']), true)
      .popup(700, 550);
  },

  reloadAdmissionLine: function(sejour_id) {
    var url = new Url("admissions", "ajax_admission_line");
    url.addParam("sejour_id", sejour_id);
    url.addParam("reloadLine", 1);
    url.requestUpdate("CSejour-"+sejour_id);
  },

  editReglementFraisSejour: function(sejour_id) {
    new Url('admissions', 'ajax_edit_sejour_frais_reglements')
      .addParam('sejour_id', sejour_id)
      .requestModal(500, null, {onClose: Admissions.reloadAdmissionLine.curry(sejour_id)});
  },

  /**
   * Met à jour le layout de vw_idx_admission en fonction des td affichés
   */
  updateAdmissionIdxLayout: function () {
    var legend_td = $('idx_admission_legend');
    $('idx_admission_legend_button').toggle();
    legend_td.colSpan = legend_td.colSpan === 2 ? 1 : 2;
  },

  /**
   * Affiche ou cache le bouton "Imprimer pour la sélection" en fonction des admissions sélectionnées
   */
  updatePrintSelectionButtonDisplay: function() {
    var admissions_checkbox = $$("input[name=print_doc]");
    var visible = false;
    admissions_checkbox.forEach(function(checkbox) {
      if (!visible && $V(checkbox)) {
        visible = true;
        $('printSelectionButton').show();
      }
    });

    if (!visible) {
      $('printSelectionButton').addClassName('disabled').setAttribute('disabled', 'disabled');
      $('send-all-presta-button').addClassName('disabled').setAttribute('disabled', 'disabled');
    } else {
      $('printSelectionButton').removeClassName('disabled').removeAttribute('disabled');
      $('send-all-presta-button').removeClassName('disabled').removeAttribute('disabled');
    }
  },

  printGlobal: function () {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_admissions");
    url.addParam("date", $V(form.date));
    url.addParam("type", $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("period", $V(form.period));
    url.addParam('type_pec[]', $V(form.elements['type_pec[]']), true);
    url.addParam('print_global', 1);

    if ($(form.name + '_reglement_dh')) {
      url.addParam('reglement_dh', $V(form.elements['reglement_dh']));
    }

    url.popup(800, 500);
  },

  printSortiesGlobal: function() {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_sorties");
    url.addFormData(form);
    url.addParam('print_global', 1);
    if ($(form.name + '_reglement_dh')) {
      url.addParam('reglement_dh', $V(form.elements['reglement_dh']));
    }

    url.popup(800, 500);
  },
}
