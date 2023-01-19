/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProtocoleSelector = {
  sForm            : null,
  sForSejour       : null,
  sChir_id         : null,
  sChir_id_easy    : null,
  sChir_view       : null,
  sLibelle         : null,
  sLibelle_easy    : null,
  sCodes_ccam      : null,
  sCodes_ccam_easy : null,
  sCote            : null,
  sDuree_prevu     : null,
  sDuree_prevu_heure : null,
  sTime_op         : null,
  sMateriel        : null,
  sExamenPerop     : null,
  sExamen          : null,
  sDepassement     : null,
  sForfait         : null,
  sFournitures     : null,
  sRques_op        : null,
  sLibelle_sejour  : null,
  sType            : null,
  sTypePec         : null,
  sFacturable      : null,
  sTypeAnesth      : null,
  sDuree_uscpo     : null,
  sDuree_preop     : null,
  sConvalescence   : null,
  sDP              : null,
  sDR              : null,
  sRques_sej       : null,
  sProtoPrescAnesth: null,
  sProtoPrescChir  : null,
  sServiceId       : null,
  sServiceId_easy  : null,
  sForceType       : null,
  sPresencePreop   : null,
  sPresencePostop  : null,
  sDureeBioNet     : null,
  sUf_hebergement_id : null,
  sUf_medicale_id  : null,
  sUf_soins_id     : null,
  sCharge_id       : null,
  sTypesRessourcesIds : null,
  sExamExtempo     : null,
  sHospitDeJour    : null,
  sProtocole_id    : null,
  _sProtocole_id   : null,
  sCodage_CCAM_chir   : null,
  sCodage_CCAM_anesth : null,
  sCodage_NGAP_sejour : null,
  new_dhe          : null,
  sPack_appFine_ids : null,
  sDocItems_guid_sejour: null,
  sDocItems_guid_operation: null,
  sRRAC            : null,
  sHour_entree_prevue : null,
  sMin_entree_prevue : null,
  sCircuit_ambu : null,
  sProtocolesOp_ids: null,
  applying_ufm: null,
  apply_op_protocole: true,
  options : {},
  isProtocoleMandatory: false,

  pop: function() {
    var oForm     = (this.sForm && getForm(this.sForm)) || getForm("editOp");

    var url = new Url("planningOp", "vw_protocoles");
    url.addParam("idex_selector", 0);
    url.addParam("dialog", 1);
    url.addParam("chir_id", oForm[this.sChir_id].value);

    if (this.sForceType) {
      url.addParam("sejour_type", this.sForceType);
    }

    url.addParam("singleType", this.sForSejour == 1 ? 'sejour': 'interv');
    //url.modal(this.options);
    url.requestModal(1000, 700, this.options);
    url.modalObject.observe("afterClose", function() {
      ProtocoleSelector.reloadInitCCAMSelector(oForm.name);
    });
  },

  set: function(protocole) {
    if (this.new_dhe) {
      return DHE.applyProtocol(protocole.protocole_id);
    }

    var oOpForm     = getForm("editOp");
    var oSejourForm = getForm("editSejour");
    var oOpFormEasy = getForm("editOpEasy");

    this.applying_ufm = !!protocole.uf_medicale_id;

    // Champs de l'intervention
    if (this.apply_op_protocole && oOpForm) {
      if (protocole.chir_id && protocole.chir_view && !$V(oOpForm[this.sChir_id])) {
        $V(oOpForm[this.sChir_view], protocole.chir_view, true);
        $V(oOpForm[this.sChir_id], protocole.chir_id, true);
      }

      $V(oOpForm[this.sServiceId], protocole.service_id, true);
      if(oOpFormEasy) {
        if (protocole.chir_id && protocole.chir_view) {
          $V(oOpFormEasy[this.sChir_id_easy]   , protocole.chir_id);
        }
        if (this.sServiceId_easy && protocole.service_id) {
          $V(oOpFormEasy[this.sServiceId_easy], protocole.service_id);
        }
        $V(oOpFormEasy[this.sLibelle_easy]   , protocole.libelle);

        /* Si l'intervention a été créée et que des codes CCAM ont été saisis, les codes du protocoles sont ajoutés à ceux déjà présents */
        if ($V(oOpFormEasy[this.sCodes_ccam_easy]) != '' && protocole.codes_ccam != '' && $V(oOpFormEasy['operation_id']) != '') {
          $V(oOpFormEasy[this.sCodes_ccam_easy], $V(oOpFormEasy[this.sCodes_ccam_easy]) + '|' + protocole.codes_ccam);
        }
        /* Si l'intervention n'a pas encore été créée ou ne contient pas de codes CCAM, on remplace la valeur du champ par celle du protocole */
        else if (protocole.codes_ccam != '' && ($V(oOpForm[this.sCodes_ccam]) == '' || $V(oOpFormEasy['operation_id']) == '')) {
          $V(oOpFormEasy[this.sCodes_ccam_easy], protocole.codes_ccam);
        }
      }

      /* Si l'intervention a été créée et que des codes CCAM ont été saisis, les codes du protocoles sont ajoutés à ceux déjà présents */
      if ($V(oOpForm[this.sCodes_ccam]) != '' && protocole.codes_ccam != '' && oCcamField != undefined && $V(oOpForm['operation_id']) != '') {
        protocole.codes_ccam.split('|').each(
          function(code) {
            oCcamField.add(code, true);
          }
        );
      }
      /* Si l'intervention n'a pas encore été créée ou ne contient pas de codes CCAM, on remplace la valeur du champ par celle du protocole */
      else if (protocole.codes_ccam != '' && ($V(oOpForm[this.sCodes_ccam]) == '' || $V(oOpForm['operation_id']) == '')) {
        $V(oOpForm[this.sCodes_ccam], protocole.codes_ccam);
      }

      $V(oOpForm[this.sLibelle],           protocole.libelle);
      $V(oOpForm[this.sPresencePreop],     protocole.presence_preop);
      $V(oOpForm[this.sPresencePreop+"_da"], protocole.presence_preop);
      $V(oOpForm[this.sPresencePostop],    protocole.presence_postop);
      $V(oOpForm[this.sPresencePostop+"_da"], protocole.presence_postop);
      $V(oOpForm[this.sDureeBioNet],       protocole.duree_bio_nettoyage);
      $V(oOpForm[this.sDureeBioNet+"_da"], protocole.duree_bio_nettoyage);
      $V(oOpForm[this.sCote],              protocole.cote);
      $V(oOpForm[this.sTypeAnesth],        protocole.type_anesth);
      $V(oOpForm[this.sTime_op],           protocole._time_op);
      $V(oOpForm[this.sMateriel],          protocole.materiel);
      $V(oOpForm[this.sExamenPerop],       protocole.exam_per_op);
      $V(oOpForm[this.sExamen],            protocole.examen);
      $V(oOpForm[this.sDuree_uscpo],       protocole.duree_uscpo);
      $V(oOpForm[this.sDuree_preop],       protocole.duree_preop);
      $V(oOpForm[this.sExamExtempo],       protocole.exam_extempo);
      $V(oOpForm[this.sProtocoleId],       protocole.protocole_id);

      if (window.updateDocItemsInput) {
        updateDocItemsInput("COperation", oOpForm[this.sDocItems_guid_operation], protocole._docitems_guid_operation);
      }

      if (oOpForm[this.sTypesRessourcesIds]) {
        var types_ressources_ids = $V(oOpForm[this.sTypesRessourcesIds]);
        if (types_ressources_ids != "") {
          types_ressources_ids += ",";
        }
        types_ressources_ids += protocole._types_ressources_ids;

        $V(oOpForm[this.sTypesRessourcesIds], types_ressources_ids);
      }

      if (oOpForm[this.sDepassement] && oOpForm[this.sForfait] && oOpForm[this.sFournitures]) {
        $V(oOpForm[this.sDepassement],       protocole.depassement, false);
        $V(oOpForm[this.sForfait],           protocole.forfait, false);
        $V(oOpForm[this.sFournitures],       protocole.fournitures, false);
      }

      $V(oOpForm[this.sRques_op], protocole.rques_operation);
      $V(oOpForm[this.sProtoPrescAnesth], protocole.protocole_prescription_anesth_id);

      /* Les codages d'actes automatiques ne sont appliqués qui si l'intervention est en création */
      if (protocole.facturation_rapide && $V(oOpForm['operation_id']) == '') {
        $V(oOpForm[this.sCodage_CCAM_chir], protocole._codage_ccam_chir);
        $V(oOpForm[this.sCodage_CCAM_anesth], protocole._codage_ccam_anesth);
      }

      $V(oOpForm[this.sProtocolesOp_ids], protocole.sProtocolesOp_ids);
    }
    else if (!$V(oSejourForm[this.sChir_id])) {
      $V(oSejourForm[this.sChir_id], protocole.chir_id, true);
      $V(oSejourForm[this.sChir_view], protocole.chir_view, true);
    }

    if (this._sProtocole_id) {
      if (oOpForm) {
        $V(oOpForm[this._sProtocole_id], protocole.protocole_id);
        if (protocole.chir_view !== "") {
          $V(oOpForm[this.sChir_view], protocole.chir_view, true);
        }
      }
      else {
        $V(oSejourForm[this._sProtocole_id], protocole.protocole_id);
      }
    }

    // Champs du séjour
    if(!oSejourForm.sejour_id.value || (parseInt(oSejourForm[this.sDuree_prevu].value) < protocole.duree_hospi)) {
      $V(oSejourForm[this.sDuree_prevu], protocole.duree_hospi);
      if (this.sType) {
        $V(oSejourForm[this.sType], protocole.type, false);
        // Préselection de l'uf médicale si nécessaire
        preselectUf();
      }
    }

    if (this.sCharge_id) {
      // La liste des mode de traitements doit être réactualisée
      if (Object.isFunction(window.updateListCPI)) {
        // car le changement du type d'hospitalisation n'appelle pas le onchange
        updateListCPI(oSejourForm, true);
      }
      var elt_charge_id = oSejourForm[this.sCharge_id];
      if (elt_charge_id) {
        $V(elt_charge_id, protocole.charge_id);
      }
    }

    if (this.sCodage_NGAP_sejour) {
      if (protocole.type == 'seances') {
        $V(oSejourForm[this.sCodage_NGAP_sejour], protocole._codage_ngap_sejour);
      }
    }

    if (parseInt(protocole.duree_heure_hospi)) {
      $V(oSejourForm[this.sDuree_prevu_heure], protocole.duree_heure_hospi);
    }

    if (window.updateDocItemsInput) {
      updateDocItemsInput("CSejour", oSejourForm[this.sDocItems_guid_sejour], protocole._docitems_guid_sejour);
    }

    if (this.sUf_hebergement_id && oSejourForm[this.sUf_hebergement_id]) {
      $V(oSejourForm[this.sUf_hebergement_id], protocole.uf_hebergement_id);
    }
    if (protocole.uf_medicale_id && this.sUf_medicale_id && oSejourForm[this.sUf_medicale_id]) {
      // Si l'option est désactivée, il faut l'activer
      var option = oSejourForm[this.sUf_medicale_id].down("option[value=" + protocole.uf_medicale_id + "]");
      if (option && option.tagName === "OPTION") {
        option.writeAttribute("disabled", false);
      }

      if (oOpFormEasy && oOpFormEasy[this.sUf_medicale_id]) {
        option = oOpFormEasy[this.sUf_medicale_id].down("option[value=" + protocole.uf_medicale_id + "]");
        if (option && option.tagName === "OPTION") {
          option.writeAttribute("disabled", false);
        }
      }

      // Dans le cas où le champ est requis, on demande confirmation pour écraser
      if (!oSejourForm[this.sUf_medicale_id].hasClassName("notNull") || !$V(oSejourForm[this.sUf_medicale_id]) || confirm($T("CSejour-Override ufm"))) {
        $V(oSejourForm[this.sUf_medicale_id], protocole.uf_medicale_id);
      }
    }
    if (this.sUf_soins_id && oSejourForm[this.sUf_soins_id]) {
      $V(oSejourForm[this.sUf_soins_id], protocole.uf_soins_id);
    }
    if (this.sTypePec) {
      $V(oSejourForm[this.sTypePec], protocole.type_pec);
    }
    if (this.sFacturable) {
      $V(oSejourForm[this.sFacturable], protocole.facturable);
    }
    if (this.sRRAC) {
      $V(oSejourForm[this.sRRAC], protocole.RRAC);
    }
    if (this.sCircuit_ambu) {
      $V(oSejourForm[this.sCircuit_ambu], protocole.circuit_ambu);
    }
    if(this.sServiceId && oSejourForm[this.sServiceId]) {
      $V(oSejourForm[this.sServiceId], protocole.service_id);
    }
    if(this.sDP && oSejourForm[this.sDP]) {
      $V(oSejourForm[this.sDP], protocole.DP);
    }
    if(this.sDR && oSejourForm[this.sDR]) {
      $V(oSejourForm[this.sDR], protocole.DR);
    }
    if (this.sHour_entree_prevue || this.sMin_entree_prevue) {
      if (parseInt(protocole.hour_entree_prevue)) {
        $V(oSejourForm[this.sHour_entree_prevue], parseInt(protocole.hour_entree_prevue));
      }
      if (parseInt(protocole.min_entree_prevue)) {
        $V(oSejourForm[this.sMin_entree_prevue], parseInt(protocole.min_entree_prevue));
      }
    }

    if (!oOpForm || !$V(oSejourForm.sejour_id)) {
      $V(oSejourForm[this.sLibelle_sejour], protocole.libelle_sejour);
    }

    if(this.sConvalescence && (oSejourForm.sejour_id.value && oSejourForm[this.sConvalescence].value)) {
      $V(oSejourForm[this.sConvalescence], oSejourForm[this.sConvalescence].value+"\n"+protocole.convalescence);
    } else {
      $V(oSejourForm[this.sConvalescence], protocole.convalescence);
    }
    if(oSejourForm.sejour_id.value && oSejourForm[this.sRques_sej].value) {
      $V(oSejourForm[this.sRques_sej], oSejourForm[this.sRques_sej].value+"\n"+protocole.rques_sejour);
    } else {
      $V(oSejourForm[this.sRques_sej], protocole.rques_sejour);
    }

    if (window.refreshListCCAM) {
      refreshListCCAM("expert");
      refreshListCCAM("easy");
    }

    if (oSejourForm[this.sProtoPrescChir] && protocole.protocole_prescription_chir_id != 'prot-') {
      $V(oSejourForm[this.sProtoPrescChir], protocole.protocole_prescription_chir_id);
      $V(oSejourForm.libelle_protocole, protocole.libelle_protocole_prescription_chir);
    }
    else {
      $V(oSejourForm.libelle_protocole, protocole.libelle_protocole_prescription_chir);
      $V(oSejourForm[this.sProtoPrescChir], "");
    }

    $V(oSejourForm[this.sHospitDeJour], protocole.hospit_de_jour);

    if (this.sPack_appFine_ids) {
      $V(oSejourForm[this.sPack_appFine_ids], protocole._pack_appFine_ids);
    }
    if (oSejourForm.code_EDS) {
      $V(oSejourForm.code_EDS, protocole.code_EDS ? protocole.code_EDS : "");
    }
    if (window.refreshViewProtocoleAnesth) {
      refreshViewProtocoleAnesth(protocole.protocole_prescription_anesth_id);
    }
    this.updateLibelle(protocole);
    this.addUnselectButton(protocole);

    this.applying_ufm = false;
  },
  reloadInitCCAMSelector: function(form_name) {
    if (window.CCAMSelector) {
      CCAMSelector.init = function(){
        var oForm     = (ProtocoleSelector.sForm && getForm(ProtocoleSelector.sForm)) || getForm("editOp");
        this.sForm  = oForm.name;
        this.sView  = "_codes_ccam";
        this.sChir  = "chir_id";
        this.sClass = "_class";
        this.pop();
      }
    }
  },
  /**
   * Update DHE labels elements with protocole data
   * @param protocole
   */
  updateLibelle: function (protocole) {
    this.sLibelle = protocole.libelle;
    this.sLibelle_easy = protocole.libelle;

    let libelle_container_row = $$('.libellesProtocolesOperatoires')

    $$('.libelleProtocole').forEach(element => {
      element.update(DOM.span({
        class: 'circled me-margin-right-5',
      }, $T('CProtocole') + ' : ' + protocole.libelle))
    });


    if (protocole.sProtocolesOp_libelles_list.length) {
      libelle_container_row.forEach(element => {
        element.removeClassName('hidden')
        let libelle_container = element.down('td');
        libelle_container.update('')
        protocole.sProtocolesOp_libelles_list.forEach((libelle) => {
          libelle_container.insert(DOM.span({
            class: 'circled me-margin-right-5',
          }, libelle))
        })
      })
    }
  },
  /**
   * Add a button to empty the form fields filled by the protocole
   * @param protocole
   */
  addUnselectButton: function (protocole) {
    let btn = DOM.button({
      className: "cancel notext me-secondary",
      type:      "button",
      onclick:   "ProtocoleSelector.unselect('" + protocole.protocole_id + "');",
      title:     $T('CProtocole.unselect')
    });

    $$('.protocoleAction').forEach(element => {
      element.update(DOM.button({
        className: "cancel notext me-secondary",
        type:      "button",
        onclick:   "ProtocoleSelector.unselect('" + protocole.protocole_id + "');",
        title:     $T('CProtocole.unselect')
      }))
    });
  },
  /**
   * Empty the forms fields filled by the protocole
   * @param protocole_id
   */
  unselect: function (protocole_id) {
    let protocole = window.aProtocoles[protocole_id],
      keys = Object.keys(protocole),
      oOpForm = getForm("editOp"),
      oSejourForm = getForm("editSejour"),
      oOpFormEasy = getForm("editOpEasy"),
      protocoleOpCont = $$(".libellesProtocolesOperatoires");

    keys.forEach(key => {
      if (key === 'codes_ccam') {
        protocole[key].split('|').forEach(code_ccam => {
          if (!$V(oOpForm.operation_id)) {
            removePrecodageCCAM(code_ccam);
          }
          oCcamField.remove(code_ccam);
        })
      }
      if (protocole[key].length !== 0) {
        if ($V(oOpForm[key])) {
          $V(oOpForm[key], '')
        } else if ($V(oSejourForm[key])) {
          $V(oSejourForm[key], '')
        } else {
          $V(oOpFormEasy[key], '')
        }
      }
    });

    if (window.refreshListCCAM) {
      refreshListCCAM("expert");
      refreshListCCAM("easy");
    }

    $$('.libelleProtocole').forEach(element => {
      element.update('')
    });

    protocoleOpCont.forEach(element => {
      element.down('td').update('')
      element.addClassName('hidden')
    });

    let btn = DOM.button({
      className: 'search notext me-tertiary',
      type:      'button',
      onclick:   'ProtocoleSelector.init();',
      title:     $T('CProtocole.select')
    })

    $$('.protocoleAction').forEach(element => {
      element.update(btn)
    });
    // Si protocole obligatoire on affiche la modale de protocole
    if (this.isProtocoleMandatory) {
      this.init();
    }
  }
};
