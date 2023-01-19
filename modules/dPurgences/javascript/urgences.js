/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Urgences = {
  pays: null,
  tabs: null,
  _responsable_id: null,
  view_mode: "infirmier",
  tab_mode: null,
  rpu_area: null,
  from_synthese: false,
  nb_printers: 0,
  callback_uhcd: null,
  callback_cnsph: null,

  hospitalize : function(rpu_id, group_id) {
    new Url("urgences", "ajax_hospitalization_rpu")
      .addParam("rpu_id", rpu_id)
      .addParam("group_id", group_id)
      .requestModal("965", "400");
  },

  checkMerge : function(sejour_id, sejour_id_futur) {
    new Url("urgences", "ajax_check_merge")
      .addParam("sejour_id", sejour_id)
      .addParam("sejour_id_futur", sejour_id_futur)
      .requestUpdate("result_merge_"+sejour_id_futur);
  },

  printDossier: function(id) {
    new Url("urgences", "print_dossier")
      .addParam("rpu_id", id)
      .popup(700, 550, "RPU");
  },

  loadRPU: function(rpu_id, sejour_id, show_rpu_consultation) {
    var rpu_area = Urgences.rpu_area || "rpu_" + rpu_id;

    if (!$(rpu_area)) {
      return;
    }

    new Url("urgences", "ajax_vw_rpu")
      .addParam("rpu_id", rpu_id)
      .addParam("sejour_id", sejour_id)
      .addParam("view_mode", Urgences.view_mode)
      .addParam("tab_mode", Urgences.tab_mode)
      .addParam("show_rpu_consultation", show_rpu_consultation)
      .addNotNullParam("_responsable_id", Urgences._responsable_id)
      .requestUpdate(rpu_area);
  },

  createPecMed: function(rpu_id) {
    new Url("urgences", "ajax_pec_med")
      .addParam("rpu_id", rpu_id)
      .requestModal();
  },

  pecMed: function(consult_id, fragment) {
    Urgences.onOpenPec();

    new Url("urgences", "edit_consultation")
      .addParam("selConsult", consult_id)
      .setFragment(fragment)
      .modal({
        width: "100%",
        height: "100%",
        onClose: Urgences.onClosePec.curry(null)
      });
  },

  pecInf: function(sejour_id, rpu_id, fragment) {
    Urgences.onOpenPec();

    new Url("urgences", "vw_aed_rpu")
      .addParam("rpu_id", rpu_id)
      .addParam("sejour_id", sejour_id)
      .addParam("fragment", fragment)
      .setFragment(fragment)
      .modal({
        width: "100%",
        height: "100%",
        onClose: Urgences.onClosePec.curry(null)
      });
  },

  onClosePec: function(sejour_id) {
    var form = getForm("selView");
    if (form && form.onsubmit) {
      form.onsubmit();
    }
    if (window.Rafraichissement) {
      if (sejour_id) {
        Rafraichissement.refreshSejour(sejour_id);
      }
      else {
        Rafraichissement.init();
      }
      Rafraichissement.start();
    }

    if (window.refreshExecuter) {
      refreshExecuter.resume();
    }
  },

  onOpenPec: function() {
    // Depuis la vue des placement
    if (window.Rafraichissement) {
      clearTimeout(Rafraichissement.handler_init);
    }

    // Depuis la vue des sorties
    if (window.refreshExecuter) {
      refreshExecuter.stop();
    }
  },

  actions: function(rpu_id, sejour_id) {
    Urgences.onOpenPec();
    new Url("urgences", "ajax_actions_rpu")
      .addParam("rpu_id", rpu_id)
      .addParam("sejour_id", sejour_id)
      .requestModal("70%", "70%", {onClose: Urgences.onClosePec.curry(sejour_id)})
  },

  updateColor: function(div, color, sejour_id) {
    var form = getForm("colorRPU");

    var selected = div.get("selected");

    if (selected == "1") {
      color = "";
    }

    var divs = $$("div.couleur_rpu");

    $V(form.color, color);
    divs.invoke("setStyle", {outline: ""});
    divs.invoke("set", "selected", "0");

    // Outline sur la couleur sélectionnée
    if (color) {
      div.setStyle({outline: "2px solid #000"});
      div.set("selected", "1");
    }

    // Background dans la vue topologique
    var div_rpu = $("placement_" + sejour_id);

    if (div_rpu) {
      div_rpu.setStyle({backgroundColor: color ? ("#" + color) : ""});
    }

    onSubmitFormAjax(form);
  },

  updateCategorie: function(div, categorie_rpu_id, sejour_id) {
    var form = getForm("categorieRPU");
    var selected = div.get("link_cat_id");

    // Suppression
    if (selected) {
      $V(form.rpu_link_cat_id, selected);
      $V(form.del, "1");

      div.setStyle({outline: ""});
      div.set("link_cat_id", "");

      Form.onSubmitComplete = Prototype.emptyFunction;
    }
    // Ajout
    else {
      $V(form.rpu_link_cat_id, "");
      $V(form.rpu_categorie_id, categorie_rpu_id);
      $V(form.del, "0");

      div.setStyle({outline: "2px solid #000"});

      Form.onSubmitComplete = function(guid, object) {
        var id = guid.split("-")[1];
        $("categorie_rpu_" + object.rpu_categorie_id).set("link_cat_id", id);
      };
    }

    onSubmitFormAjax(form, function() {
      // Refresh du séjour dans la vue topologique
      if (window.Rafraichissement) {
        Rafraichissement.refreshSejour(sejour_id);
      }
    });
  },

  synthese: function(consult_id, sejour_id) {
    Urgences.onOpenPec();
    new Url("urgences", "edit_consultation")
      .addParam("selConsult", consult_id)
      .addParam("synthese_rpu", 1)
      .modal({
        width: "100%",
        height: "100%",
        showReload: true,
        onClose: Urgences.onClosePec
      });
  },

  syntheseConsult: function(rpu_id) {
    new Url("urgences", "vw_synthese_rpu")
      .addParam("rpu_id", rpu_id)
      .requestUpdate("synthese_rpu");
  },

  syntheseRPU: function(rpu_id) {
    Urgences.onOpenPec();
    new Url("urgences", "vw_synthese_rpu")
      .addParam("rpu_id", rpu_id)
      .modal({
        width: "100%",
        height: "100%",
        showReload: true,
        onClose: Urgences.onClosePec
      });
  },

  timelineSejour: function(sejour_id) {
    new Url("urgences", "ajax_timeline_sejour")
      .addParam("sejour_id", sejour_id)
      .requestUpdate("timeline_sejour");
  },

  modalSortie: function(callback) {
    if (!Urgences.from_synthese) {
      callback();
      return;
    }

    Modal.open("sortie_rpu", {onClose: callback});
  },

  cancelRPU: function() {
    var oForm = getForm("editRPU");
    var oElement = oForm._annule;

    if (oElement.value == "0") {
      if (confirm("Voulez-vous vraiment annuler le dossier ?")) {
        oElement.value = "1";
        oForm.submit();
        return;
      }
    }

    if (oElement.value == "1") {
      if (confirm("Voulez-vous vraiment rétablir le dossier ?")) {
        oElement.value = "0";
        oForm.submit();
        return;
      }
    }
  },

  printEtiquettes: function(rpu_id) {
    if (Urgences.nb_printers > 0) {
      new Url("compteRendu", "ajax_choose_printer")
        .addParam("mode_etiquette", 1)
        .addParam("object_class", "CRPU")
        .addParam("object_id", rpu_id)
        .requestModal(400);
      return;
    }

    new Url("hospi", "print_etiquettes", "raw")
      .addParam("object_class", "CRPU")
      .addParam("object_id", rpu_id)
      .open();
  },

  toggleModeEntree: function(mode_entree) {
    var container = $("duree_prevue_container");

    if (!container) {
      return;
    }

    container[mode_entree === "6" ? "show" : "hide"]();
  },

  reloadSortieReelle: function() {
    new Url("urgences", "ajax_sortie_reelle")
      .addParam("sejour_id", getForm('editSortieReelle').elements.sejour_id.value)
      .addParam("consult_id", getForm('ValidCotation').elements.consultation_id.value)
      .requestUpdate('div_sortie_reelle');
  },
  /**
   * Edit the reevaluate PEC
   *
   * @param rpu_reeval_pec_id
   * @param rpu_id
   * @param main_courant
   */
  editReevaluatePEC: function (rpu_reeval_pec_id, rpu_id, main_courant) {
    new Url("urgences", "ajax_edit_reevaluate_pec")
      .addParam("rpu_reeval_pec_id", rpu_reeval_pec_id)
      .addParam("rpu_id", rpu_id)
      .requestModal(null, null, {onClose: function() {
          if (main_courant) {
            Urgences.onClosePec(null);
          }
          else {
            Urgences.loadRPU(rpu_id);
          }
        }});
  },

  criteresUHCD: function(rpu_id) {
    new Url('urgences', 'ajax_criteres_uhcd')
      .addParam('rpu_id', rpu_id)
      .requestModal('600px');
  },

  valideCriteres: function(form) {
    if ($V(form.decision_uhcd) === '0'
        || ($V(form.diag_incertain_pec) === '0' && $V(form.caractere_instable) === '0')
        || $V(form.surv_hosp_specifique) === '0'
    ) {
      alert($T('CRPU-One criteria mandatory missing'));
      return false;
    }

    onSubmitFormAjax(
      form,
      function() {
        Control.Modal.close();
        Urgences.callback_uhcd();
      }
    );
  },
  /**
   * Forbidden the stay mutation to hospitalization
   *
   * @param element
   * @param config_interdire_mutation_hospit
   * @param mutation_sejour_id
   */
  forbiddenMutationToHospitalization: function (element, config_interdire_mutation_hospit, mutation_sejour_id) {
    $$('button.autoriser_sortie').invoke('writeAttribute', 'disabled', null);

    if ((element.value === 'mutation') && (config_interdire_mutation_hospit === '1') && !mutation_sejour_id) {
      alert($T('CRPU-msg-interdire_mutation_hospit'));
      $$('button.autoriser_sortie').invoke('writeAttribute', 'disabled', 'disabled');
    }

    return true;
  },
  /**
   * Onchange mode sortie
   *
   * @param element
   * @param config_interdire_mutation_hospit
   * @param mutation_sejour_id
   */
  onchangeModeSortie: function (element, config_interdire_mutation_hospit, mutation_sejour_id) {
    if (Urgences.forbiddenMutationToHospitalization(element, config_interdire_mutation_hospit, mutation_sejour_id)) {
      changeOrientation(element);
      Fields.init(element.value);
      element.form.onsubmit();
    }
  },

  verifyNbInscription: function (rpu_id, callback) {
    new Url("urgences", "verifyNbInscription")
      .addParam("rpu_id", rpu_id)
      .requestJSON(function (data) {
        if (data.haveInscription) {
          new Url("urgences", "infoVerifyNbInscription")
            .addParam("callback", callback)
            .addParam("rpu_id", rpu_id)
            .requestModal();
        } else {
          eval(callback)();
        }
      })
  },

  showDossierPrescription: function(sejour_id) {
    Control.Modal.close();
    let tab = $$('a[href=#prescription_sejour]')[0];
    tab.click();
    Prescription.reloadPrescSejour('', sejour_id,'', null, null, null,'', null, false);
  }
};
