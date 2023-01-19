/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProtocoleDHE = window.ProtocoleDHE || {
  dialog:           null,
  appFine:          null,
  sejourType:       null,
  canEditProtocole: true,
  showOnlyChargePriceIndicator: false,

  popupImport: function () {
    return new Url("planningOp", "protocole_dhe_import_csv")
      .popup(800, 600, "Import des Protocoles de DHE");
  },

  popupExport: function () {
    var formFrom = getForm("selectFrm");
    var formTo = getForm("exportProtocoles");
    $V(formTo.chir_id, $V(formFrom.chir_id));
    $V(formTo.function_id, $V(formFrom.function_id));
    $V(formTo.idx_tags, $V(formFrom.tags_to_search));
    $V(formTo.exclude_no_idx, $V(formFrom.exclude_no_idx));
    formTo.submit();
  },

  popupExportAll: function () {
    var form = getForm("exportProtocoles");
    $V(form.chir_id, "");
    $V(form.function_id, "");
    form.submit();
  },

  chooseProtocole: function (protocole_id) {
    if (this.dialog && protocole_id) {
      return this.setClose(protocole_id);
    }
    if (this.canEditProtocole) {
      new Url("planningOp", "vw_edit_protocole")
        .addParam("protocole_id", protocole_id)
        .requestModal(900, 700);
    }
  },

  setClose: function (protocole_id) {
    if (aProtocoles[protocole_id]) {
      ProtocoleSelector.set(aProtocoles[protocole_id]);
      Control.Modal.close();
    } else {
      new Url("planningOp", "ajax_get_protocole")
        .addParam("protocole_id", protocole_id)
        .addParam("chir_id", $V(getForm("selectFrm").chir_id))
        .requestUpdate("get_protocole");
    }
  },

  refreshList: function (form, types, reset, appFine_pack_id) {
    types = types || ["interv", "sejour"];
    if (reset) {
      types.each(function (type) {
        $V(form.elements["page[" + type + "]"], 0, false);
      });
    }

    var inactive = $("show_inactive_checkbox") ? $("show_inactive_checkbox").checked : false;

    var url = new Url("planningOp", "httpreq_vw_list_protocoles")
      .addParam("chir_id", $V(form.chir_id))
      .addParam("dialog", $V(form.dialog))
      .addParam("function_id", $V(form.function_id))
      .addParam("sejour_type", this.sejourType)
      .addParam("tags_to_search", $V(form.tags_to_search))
      .addParam("exclude_no_idx", $V(form.exclude_no_idx));

    if (appFine_pack_id) {
      url.addParam('appFine_pack_id', appFine_pack_id);
    }

    if (inactive) {
      url.addParam("inactive", inactive ? 1 : 0);
    }
    types.each(function (type) {
      url.addParam("page[" + type + "]", $V(form["page[" + type + "]"]))
        .addParam("type", type)
        .requestUpdate(type);
    });
  },

  changePagesejour: function (page) {
    $V(getForm("selectFrm").elements["page[sejour]"], page);
  },

  changePageinterv: function (page) {
    var form = getForm("selectFrm");
    var show_inactive_checkbox = $("show_inactive_checkbox");
    $V(form.inactive, show_inactive_checkbox ? (show_inactive_checkbox.checked ? "1" : "") : '');
    $V(form.elements["page[interv]"], page);
  },

  addBesoins: function (types_ressources_ids) {
    if (!this.dialog) {
      return false;
    }
    var form = getForm("addBesoinProtocole");

    types_ressources_ids.split(",").each(function (type_ressource_id) {
      $V(form.type_ressource_id, type_ressource_id);
      onSubmitFormAjax(form);
    });
  },

  addPacksAppFine: function (pack_ids) {
    if (!this.appFine) {
      return false;
    }
    var form = getForm("addPackProtocole");

    pack_ids.split(",").each(function (pack_id) {
      $V(form.pack_id, pack_id);
      onSubmitFormAjax(form);
    });
  },

  controleDurees: function () {
    new Url("planningOp", "vw_controle_durees")
      .requestModal("80%", "80%");
  },

  initVwProtocoles: function (dialog, appFine, singletype, sejourType) {
    ProtocoleDHE.dialog = dialog;
    ProtocoleDHE.appFine = appFine;
    ProtocoleDHE.sejourType = sejourType;

    var oForm = getForm("selectFrm");

    this.refreshList(oForm, null, null, false);

    var url = new Url("planningOp", "ajax_protocoles_autocomplete");
    url.addParam("field", "protocole_id");
    url.addParam("input_field", "search_protocole");
    if (singletype === "interv") {
      url.addParam("for_sejour", "0");
    }
    if (singletype === "sejour") {
      url.addParam("for_sejour", "1");
    }
    url.autoComplete(oForm.elements.search_protocole, null, {
      minChars:           3,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        this.chooseProtocole(selected.id.split('-')[2]);
        $V(field, "");
      }.bind(this),
      callback:           function (input, queryString) {
        var chir_id = $V(input.form.chir_id);
        var function_id = $V(input.form.function_id);
        var search_all_protocole = $V(input.form.search_all_protocole);
        return queryString + '&chir_id=' + chir_id + '&function_id=' + function_id
          + (input.form.search_all_protocole ? '&search_all_protocole=' + search_all_protocole : '');
      }
    });
  },


  synchronizeTypesPacksAppFine: function (types) {
    // Réinitialisation des packs du protocole
    window.packs_non_stored = [];
    window.packs_non_stored = types.split(",");
  },

  copier: function (isPraticien, mediuserId) {
    var oForm = getForm("editProtocole");
    oForm.protocole_id.value = "";
    if (isPraticien) {
      oForm.chir_id.value = mediuserId;
    }
    if (oForm.libelle.value) {
      oForm.libelle.value = "Copie de " + oForm.libelle.value;
    } else {
      oForm.libelle.value = "Copie de " + oForm.codes_ccam.value;
    }
    oForm.onsubmit = function () {
      onSubmitFormAjax(this);
    };
    $V(oForm.callback, "ProtocoleDHE.afterCopier");
    oForm.onsubmit();
  },

  afterCopier: function (id) {
    this.refreshList(getForm("selectFrm"), null, false);
    Control.Modal.close();
    this.chooseProtocole(id);
  },

  refreshListCCAMProtocole: function () {
    var oCcamNode = $("listCodesCcamProtocole");

    var oForm = getForm("editProtocole");
    $V(oForm._codes_ccam, "");
    var aCcam = oForm.codes_ccam.value.split("|");
    // Si la chaine est vide, il crée un tableau à un élément vide donc :
    aCcam = aCcam.without("");

    var iCode = 0;
    var sCode;
    var listCodageChir = $('listCodageCCAM_chir');
    var listCodageAnesth = $('listCodageCCAM_anesth');

    oCcamNode.update("");
    while (sCode = aCcam[iCode++]) {
      sCode = sCode.htmlSanitize();
      oCcamNode.insert(DOM.button({
        className: 'remove',
        type:      'button',
        onclick:   ' ProtocoleDHE.removeCodageCCAM(\"' + sCode + '\"); oCcamFieldProtocole.remove(\"' + sCode + '\");'
      }, sCode));
    }

    listCodageChir.update('');
    if ($V(oForm.codage_ccam_chir) != '') {
      $V(oForm.codage_ccam_chir).split('|').each(function (codage) {
        codage = codage.split('-');
        var span = DOM.span({
          className: codage[0]
        }, codage[0]);
        span.insert(DOM.span({className: 'circled'}, codage[1] + '-' + codage[2]));
        codage[3].toArray().each(function (mod) {
          span.insert(DOM.span({className: 'circled'}, mod));
        });
        listCodageChir.insert(span);
      });
    }

    listCodageAnesth.update('');
    if ($V(oForm.codage_ccam_anesth) != '') {
      $V(oForm.codage_ccam_anesth).split('|').each(function (codage) {
        codage = codage.split('-');
        var span = DOM.span({
          className: codage[0]
        }, codage[0]);
        span.insert(DOM.span({className: 'circled'}, codage[1] + '-' + codage[2]));
        codage[3].toArray().each(function (mod) {
          span.insert(DOM.span({className: 'circled'}, mod));
        });
        listCodageAnesth.insert(span);
      });
    }

    listCodageChir
      .insert(DOM.button({
      className: 'edit notext',
      type:      'button',
      onclick:   'ProtocoleDHE.codeProtocole("chir")'
    }, 'Codage CCAM chir'))

    listCodageAnesth
      .insert(DOM.button({
      className: 'edit notext',
      type:      'button',
      onclick:   'ProtocoleDHE.codeProtocole("anesth")'
    }, 'Codage CCAM anesth'))

    if ($V(oForm.codes_ccam) != '' && $V(oForm.facturation_rapide) == '1') {
      listCodageChir.show();
      listCodageAnesth.show();
    } else {
      listCodageChir.hide();
      listCodageAnesth.hide();
    }
  },

  removeCodageCCAM: function (code) {
    var form = getForm("editProtocole");
    var old_codage_chir = $V(form.codage_ccam_chir);
    var new_codage_chir = old_codage_chir.split('|');
    old_codage_chir.split('|').each(function (codage, index) {
      if (codage.search(code) != -1) {
        new_codage_chir.splice(index, 1);
        throw $break;
      }
    });

    var old_codage_anesth = $V(form.codage_ccam_anesth);
    var new_codage_anesth = old_codage_anesth.split('|');
    old_codage_anesth.split('|').each(function (codage, index) {
      if (codage.search(code) != -1) {
        new_codage_anesth.splice(index, 1);
        throw $break;
      }
    });

    $V(form.codage_ccam_chir, new_codage_chir.join('|'));
    $V(form.codage_ccam_anesth, new_codage_anesth.join('|'));
  },

  checkFormSejour: function () {
    var oForm = getForm("editProtocole");
    return this.checkDureeProtocole() && checkForm(oForm) && this.checkDureeHospiProtocole() && this.checkCCAMProtocole();
  },

  checkCCAMProtocole: function () {
    var oForm = getForm("editProtocole");
    if ($V(oForm.for_sejour) == 1) {
      return true;
    }

    var sCcam = $V(oForm._codes_ccam);
    if (sCcam != "") {
      if (!oCcamFieldProtocole.add(sCcam, true)) {
        return false;
      }
    }
    oCcamFieldProtocole.remove("XXXXXX");
    var sCodesCcam = oForm.codes_ccam.value;
    var sLibelle = oForm.libelle.value;
    if (sCodesCcam == "" && sLibelle == "") {
      alert("Veuillez indiquer un acte ou remplir le libellé");
      oForm.libelle.focus();
      return false;
    }
    return true;
  },

  checkDureeHospiProtocole: function () {
    var form = getForm("editProtocole");
    if ($V(form.for_sejour) == 1) {
      return true;
    }

    field1 = form.type;
    field2 = form.duree_hospi;
    if (field1 && field2) {
      if (field1.value == "comp" && (field2.value == 0 || field2.value == '')) {
        field2.value = prompt("Veuillez saisir une durée prévue d'hospitalisation d'au moins 1 jour", "1");
        field2.onchange();
        field2.focus();
        return false;
      }
      if (field1.value == "ambu" && field2.value != 0 && field2.value != '') {
        alert('Pour une admission de type Ambulatoire, la durée du séjour doit être de 0 jour.');
        field2.focus();
        return false;
      }
    }
    return true;
  },


  checkDureeProtocole: function () {
    var form = getForm("editProtocole");

    // Si mode séjour
    if ($V(form.for_sejour) == 1) {
      return true;
    }

    field1 = form.temp_operation;

    if ($V(field1) == "00:00:00") {
      $V(field1, '');
    }
    if (field1 && $V(field1) == "") {
      alert("Temps opératoire invalide");
      field1.focus();
      return false;
    }
    return true;
  },

  setOperationActive: function (active) {
    $('operation').setOpacity(active ? 1 : 0.4)
      .select('input, button, select, textarea').each(Form.Element[active ? 'enable' : 'disable']);

    if (active) {
      $('row_codage_ngap_sejour').hide();
      $V(getForm('editProtocole').elements['codage_ngap_sejour'], '');
    } else if ($V(getForm('editProtocole').elements['type']) == 'seances') {
      $('row_codage_ngap_sejour').show();
    }
  },

  fillClass: function (element_id, element_class) {
    var split = $V(element_id).split("-");
    var classe = split[0] == "prot" ? "CPrescription" : "CPrescriptionProtocolePack";
    element_class.value = classe;
    element_id.value = split[1] ? split[1] : '';
  },

  applyModifProtocole: function () {
    var form = getForm("editProtocole");
    var type_protocole = ["interv"];
    if ($V(form.for_sejour) == 1) {
      type_protocole = ["sejour"];
    }
    this.refreshList(getForm("selectFrm"), type_protocole, false);
    Control.Modal.close();
  },

  toggleCodageButton: function (button) {
    var oForm = getForm("editProtocole");
    if (button.value == 1 && $V(oForm.codes_ccam) != '') {
      $('listCodageCCAM_chir').show();
      $('listCodageCCAM_anesth').show();
    } else {
      $('listCodageCCAM_chir').hide();
      $('listCodageCCAM_anesth').hide();
    }
  },

  codeProtocole: function (role) {
    var form = getForm('editProtocole');
    var url = new Url('planningOp', 'ajax_codage_protocole')
      .addParam('codes_ccam', $V(form.codes_ccam));
    if ($V(form.chir_id)) {
      url.addParam('chir_id', $V(form.chir_id));
    } else {
      url.addParam('function_id', $V(form.function_id));
    }
    if ($V(form.codage_ccam_chir)) {
      url.addParam('codage_ccam_chir', $V(form.codage_ccam_chir));
    }
    if ($V(form.codage_ccam_anesth)) {
      url.addParam('codage_ccam_anesth', $V(form.codage_ccam_anesth));
    }
    if ($V(form.codage_ngap_sejour)) {
      url.addParam('codage_ngap_sejour', $V(form.codage_ngap_sejour));
    }

    url.addParam('role', role)
      .addParam('object_class', 'CProtocole');

    url.requestModal(-10, -50, {
      showClose:     0,
      showReload:    0,
      method:        'post',
      getParameters: {m: 'planningOp', a: 'ajax_codage_protocole'}
    });
  },

  onChangeType: function (input, cpi) {
    /* Update the Charge Price Indicators */
    if (cpi) {
      ProtocoleDHE.updateListCPI(input.form);
    }

    if (input.value == 'ambu' || input.value == 'seances') {
      $V(input.form.duree_hospi, '0');
    }

    if ($('circuit_ambu')) {
      if (input.value == 'ambu') {
        $('circuit_ambu').show();
      } else {
        $('circuit_ambu').hide();
      }
    }

    if (input.value == 'seances' && $V(input.form.elements['for_sejour']) == '1') {
      $('row_codage_ngap_sejour').show();
    } else {
      $('row_codage_ngap_sejour').hide();
      $V(input.form.elements['codage_ngap_sejour'], '');
    }

    ProtocoleDHE.editHour();
  },

  editHour: function () {
    var form = getForm('editProtocole');
    if (form.duree_hospi.value == 0 && (form.type.value == "ambu" || form.type.value == 'seances')) {
      $('duree_heure_hospi_view').show();
    } else {
      $('duree_heure_hospi_view').hide();
      form.duree_heure_hospi.value = 0;
    }
  },

  updateListCPI: function (form) {
    var field = form.charge_id;

    if (field) {
      if (field.type == "hidden") {
        $V(field, ""); // To check the field
      }

      $A(field.options).each(function (option) {
        option.show();
        option.disabled = null;

        if (ProtocoleDHE.showOnlyChargePriceIndicator) {
          if (option.value && $V(form.type) && option.get("type") != $V(form.type)) {
            option.hide();
            option.disabled = true;
          }
        }
      });

      // If the selected one is disabled, we select the first not disabled
      var selected = field.options[field.selectedIndex];
      if (selected && selected.disabled) {
        for (var i = 0, l = field.options.length; i < l; i++) {
          var option = field.options[i];
          if (!option.disabled) {
            option.selected = true;
            break;
          }
        }
      }

      if (field.onchange) {
        // Trigger onchange to tell the form checker that the fiels has a value, and to set sejour type
        field.onchange();
      }

      // On force le ui:change car le champ vient d'être rafraichi
      field.fire("ui:change");
    }
  },

  stats: function () {
    var form = getForm('selectFrm');
    new Url('planningOp', 'vw_stats_protocoles')
      .addParam('chir_id', $V(form.chir_id))
      .addParam('function_id', $V(form.function_id))
      .requestModal('80%', '80%');
  },

  codes: {
    subjectId:     null,
    role:          null,
    objectClass:   null,
    refreshCoding: function () {
      Control.Modal.close();
      new Url('planningOp', 'ajax_codage_protocole')
        .addParam('model_id', this.subjectId)
        .addParam('role', this.role)
        .addParam('object_class', this.objectClass)
        .requestModal(-10, -50, {
          showClose:     0,
          showReload:    0,
          method:        'post',
          getParameters: {m: 'planningOp', a: 'ajax_codage_protocole'}
        });
    },

    changeCodageMode: function (element, codage_id) {
      var codageForm = getForm("formCodageRules_codage-" + codage_id);
      if ($V(element)) {
        $V(codageForm.association_mode, "user_choice");
      } else {
        $V(codageForm.association_mode, "auto");
      }
      codageForm.onsubmit();
    },

    onChangeDepassement: function (element, view, prefDefaultQualifDepense) {
      if (prefDefaultQualifDepense !== '') {
        if ($V(element)) {
          $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, prefDefaultQualifDepense);
        } else {
          $V(getForm('codageActeMotifDepassement-' + view).motif_depassement, '');
        }
      }

      this.syncCodageField(element, view);
    },

    syncCodageField: function (element, view) {
      var acteForm = getForm('codageActe-' + view);
      var fieldName = element.name;
      var fieldValue = $V(element);
      $V(acteForm[fieldName], fieldValue);
      if ($V(acteForm.acte_id)) {
        acteForm.onsubmit();
      } else {
        this.checkModificateurs(view, element);
      }
    },

    checkModificateurs: function (acte, input) {
      var exclusive_modifiers = ['F', 'P', 'S', 'U', 'O'];
      var checkboxes = $$('input[data-acte="' + acte + '"].modificateur');
      var nb_checked = 0;
      var exclusive_modifier = '';
      var exclusive_modifier_checked = false;
      checkboxes.each(function (checkbox) {
        if (checkbox.checked) {
          nb_checked++;
          if (checkbox.get('double') == 2) {
            nb_checked++;
          }
          if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
            exclusive_modifier = checkbox.get('code');
            exclusive_modifier_checked = true;
          }
        }
      });

      checkboxes.each(function (checkbox) {
        if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
          checkbox.disabled = (!checkbox.checked && nb_checked == 4) || checkbox.get('price') == '0' ||
            (exclusive_modifiers.indexOf(exclusive_modifier) != -1 && exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked);
        }
      });

      if (input) {
        var container = input.up();
        if (input.checked && container.hasClassName('warning')) {
          container.removeClassName('warning');
          container.addClassName('error');
        } else if (!input.checked && container.hasClassName('error')) {
          container.removeClassName('error');
          container.addClassName('warning');
        }
      }
    },

    setRule: function (element, codage_id) {
      var codageForm = getForm("formCodageRules_codage-" + codage_id);
      $V(codageForm.association_mode, "user_choice", false);
      var inputs = document.getElementsByName("association_rule");
      for (var i = 0; i < inputs.length; i++) {
        inputs[i].disabled = false;
      }
      $V(codageForm.association_rule, $V(element), false);
      codageForm.onsubmit();
    },

    // switchViewActivite : function(value, activite) {
    //   $$('.activite-'+activite).invoke(value ? 'show' : 'hide');
    // },

    addActeAnesthComp: function (acte, auto, CCAMFieldObject, subjectGuid) {
      if (auto || confirm("Voulez-vous ajouter l'acte d'anesthésie complémentaire " + acte + '?')) {
        var on_change = CCAMFieldObject.options.onChange;
        CCAMFieldObject.options.onChange = Prototype.emptyFunction;
        CCAMFieldObject.add(acte, true);
        onSubmitFormAjax(getForm('addActes-' + subjectGuid));
        CCAMFieldObject.options.onChange = on_change;
      }
    },

    setCodage: function (objectClass, role, subjectGuid) {
      /* Handle the CCAM coding */
      if (role == 'anesth' || role == 'chir') {
        var form_acts = $$('form.form-act');
        var full_codes = [];
        form_acts.each(function (form) {
          if ($V(form.acte_id)) {
            var modifs = '';
            form.select('input[type="checkbox"][checked="checked"]').each(function (input) {
              modifs = modifs + input.readAttribute('name').substr(13, 1);
            });
            var full_code = $V(form.code_acte) + '-' + $V(form.code_activite) + '-' + $V(form.code_phase)
              + '-' + modifs + '-' + $V(form.montant_depassement).replace('-', '*') + '-' + $V(form.code_association)
              + '-' + $V(form.rembourse) + '---' + $V(form.code_extension) + '-';

            if (form.extension_documentaire) {
              full_code = full_code + $V(form.extension_documentaire);
            }

            full_code = full_code + '-';
            if (form.position_dentaire && $V(form.position_dentaire) != '') {
              full_code = full_code + $V(form.position_dentaire).replace(/\|/g, '+');
            }

            full_code = full_code + '-' + $V(form.motif_depassement);
            console.log('facturable: %s', $V(form.facturable));
            full_code = full_code + '--' + $V(form.facturable);

            full_codes.push(full_code);
          }
        });

        var codes_ccam = $V(getForm('addActes-' + subjectGuid).codes_ccam);

        if (objectClass === 'CProtocole') {
          var formProtocole = getForm('editProtocole');
          $V(formProtocole.codes_ccam, codes_ccam);
          $V(formProtocole['codage_ccam_' + role], full_codes.join('|'));
          ProtocoleDHE.refreshListCCAMProtocole();
        } else if (objectClass === 'COperation') {
          var formOp = getForm('editOp');
          $V(formOp.codes_ccam, codes_ccam);
          $V(formOp['_codage_ccam_' + role], full_codes.join('|'));
          updateTokenCcam();
        }
      } else if (role == 'ngap') {
        var forms = $$('form[name*="editActeNGAP"]');
        var codes = [];

        forms.each(
          function (form) {
            if ($V(form.elements['acte_ngap_id'])) {
              var code = $V(form.elements['quantite'])
                + '-' + $V(form.elements['code'])
                + '-' + $V(form.elements['coefficient'])
                + '-' + $V(form.elements['montant_base'])
                + '-' + $V(form.elements['montant_depassement']).replace('-', '*')
                + '-' + $V(form.elements['demi'])
                + '-' + $V(form.elements['complement'])
                + '-' + $V(form.elements['gratuit']);

              codes.push(code);
            }
          }
        );

        $V(getForm('editProtocole').elements['codage_ngap_sejour'], codes.join('|'));
        var list = $('list_codage_ngap_sejour');
        list.update();
        codes.each(
          function (code, index) {
            code = code.split('-');
            var code_str = '';
            if (code[0] > 1) {
              code_str = code[0] + 'x';
            }
            code_str = code_str + code[1];
            if (code[2] != 1) {
              code_str = code_str + code[2];
            }

            if (index != codes.length - 1) {
              code_str = code_str + ' ';
            }

            list.insert(DOM.span(null, code_str));
          }
        );
      }
    },

    submitFormAct: function (form) {
      if ($V(form.acte_id) == '' && form.position_dentaire && $V(form.position_dentaire) == '') {
        this.setDents(form);
        return false;
      } else {
        return onSubmitFormAjax(form, function () {
          this.refreshCoding();
        }.bind(this));
      }
    },

    setDents: function (form) {
      new Url('ccam', 'setDentsCodage')
        .addParam('acte_view', form.get('view'))
        .addParam('code', $V(form.code_acte))
        .addParam('activite', $V(form.code_activite))
        .addParam('phase', $V(form.code_phase))
        .addParam('date', $V(form.execution))
        .requestModal();
    },
  },

  //Autocomplete : Insertion d'un tag d'identifiant externe dans la Dom
  insertTag: function (guid, name, dom) {
    var tag = $("idex_tag_main-" + guid);

    if (!tag) {

      var btn_main = DOM.button({
        "type":      "button",
        "className": "delete",
        "onclick":   "window.tag_to_search_token.remove($(this).up('li').get('tag_item_id')); this.up().remove(); getElementById('idex_tag_modal-" + guid + "').remove(); Event.stop(event);"
      });

      var li_main = DOM.li({
        "data-tag_item_id": guid,
        "id":               "idex_tag_main-" + guid,
        "className":        "tag"
      }, name, btn_main);


      $("search-protocol-idex-tags_modal_main").insert(li_main);

      var btn_modal = DOM.button({
        "type":      "button",
        "className": "delete",
        "onclick":   "window.tag_to_search_token.remove($(this).up('li').get('tag_item_id')); this.up().remove(); getElementById('idex_tag_main-" + guid + "').remove(); Event.stop(event);"
      });

      var li_modal = DOM.li({
        "data-tag_item_id": guid,
        "id":               "idex_tag_modal-" + guid,
        "className":        "tag"
      }, name, btn_modal);

      $(dom).insert(li_modal);

    }
  },

  removeAllTags: function (dom) {
    $(dom).update();
  },

  updateAllDurations: function (form) {
    if (confirm($T('CProtocole-update_all_protocols_durations_confirm'))) {
      var btn = $('updateAllDurationsButton');
      btn.addClassName('loading');
      return onSubmitFormAjax(
        form, {
          onComplete: function () {
            window.refreshListProtocoles();
            btn.removeClassName('loading');
          }
        }
      );
    }
    return false;
  }
};
