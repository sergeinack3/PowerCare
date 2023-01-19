{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPprescription"|module_active}}
  {{mb_script module=prescription script=prescription_editor}}
  {{mb_script module=prescription script=prescription}}
{{/if}}

{{mb_script module=planningOp script=protocole_selector}}
{{mb_script module=planningOp script=operation}}

{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=file}}
{{mb_default var=protocole_mandatory value=0}}

{{assign var=mode_easy value=$conf.dPplanningOp.COperation.mode_easy}}

{{assign var=protocole_mandatory value=0}}
{{if !$op->_id}}
    {{assign var=protocole_mandatory value="Ox\Mediboard\PlanningOp\CProtocole::isProtocoleMandatory"|static_call:null}}
{{/if}}

{{mb_default var=contextual_call value=false}}

<script>
  Main.add(function() {
    window.packs_non_stored = [];

    new TogglePairEffect("modeEasy", "modeExpert", {
      idFirstVisible: {{$app->user_prefs.mode_dhe+1}}
    });

    $('me-planning-actions').addClassName('displayed');

    // Il faut sauvegarder le sejour_id pour la création de l'affectation
    // après la fermeture de la modale.
    {{if $op->_id && $op->_ref_sejour && $dialog == 1 && !$ext_cabinet_id}}
      window.parent.sejour_id_for_affectation = '{{$op->_ref_sejour->_id}}';
    {{/if}}

    {{if !$op->_id}}
      ProtocoleSelector.isProtocoleMandatory = {{$protocole_mandatory}}
    {{/if}}
  });

  function printDocument(iDocument_id) {
    var form = document.editOp;

    if (checkFormOperation() && (iDocument_id.value != 0)) {
      var url = new Url("compteRendu", "edit");
      url.addElement(form.operation_id, "object_id");
      url.addElement(iDocument_id, "modele_id");
      url.popup(700, 600, "Document");
      return true;
    }

    return false;
  }

  function printPack(iPack_id) {
    var form = document.editOp;

    if (checkFormOperation() && (iPack_id.value != 0)) {
      var url = new Url("compteRendu", "edit");
      url.addElement(form.operation_id, "object_id");
      url.addElement(iPack_id, "pack_id");
      url.popup(700, 600, "Document");
      return true;
    }

    return false;
  }

  function printForm() {
    var url = new Url("planningOp", "view_planning");
    url.addElement(document.editOp.operation_id);
    url.popup(700, 500, "printPlanning");
    return;
  }

  function checkMaxIntervPlageOp() {
    var msg = "";
    var form = getForm('editOp');
    var formSejour = getForm('editSejour');
    if (!$V(form.operation_id) && ($V(formSejour.type) == 'ambu' || $V(formSejour.type) == 'comp')) {
      var url = new Url('planningOp', 'ajax_check_max_interv');
      url.addParam('plageop_id', $V(form.plageop_id));
      url.addParam('type'      , $V(formSejour.type));
      url.requestJSON(function(error) {
        if (error) {
          msg = 'Nombre maximum d\'interventions de type hospitalisation complète atteint pour cette plage opératoire';
          if ($V(formSejour.type) == 'ambu') {
            msg = 'Nombre maximum d\'interventions de type ambulatoire atteint pour cette plage opératoire';
          }
          alert(msg);
        }
      }, {asynchronous: false});
    }
    return msg;
  }

  function submitForms() {
    var oSejourForm = getForm("editSejour");
    var oFormOperation = getForm("editOp");
    if (!checkDureeHospi() || !checkForm(oSejourForm) || !checkFormOperation()) {
      return;
    }

    if (!Operation.checkOperationInStay(oFormOperation, oSejourForm)) {
      return;
    }

    if (checkMaxIntervPlageOp()) {
      return;
    }

    askNDA(oSejourForm, function() {
      // On n'appliquera le protocole de prescription qu'à l'enregistrement de l'intervention
      $V(oFormOperation._protocole_prescription_chir_id, $V(oSejourForm._protocole_prescription_chir_id));
      $V(oSejourForm._protocole_prescription_chir_id, "");
      onSubmitFormAjax(oSejourForm);
    });
  }

  function submitFormOperation(sejour_id) {
    if (sejour_id) {
      var form = getForm("editOp");
      $V(form.sejour_id, sejour_id);

      if (window.statusDHE && window.statusDHE.length) {
        $V(form.callback, "createMultipleDHE");
        return onSubmitFormAjax(form);
      }
      if (form.onsubmit()) {
        form.submit();
      }
    }
  }

  function createMultipleDHE() {
    var statusDHE = window.statusDHE.shift();

    var form = getForm("editSejour");

    // La date sera calculée automatiquement par la fonction PlageOpSelector.set
    $V(form._date_entree_prevue, "", false);

    // Pas d'application du protocole de prescription sur les séjours futurs
    $V(getForm("editOp")._protocole_prescription_chir_id, "");

    PlageOpSelector.set(statusDHE.form, '', '', '', onSubmitFormAjax.curry(form));
  }

  function deleteLineDHEMultiple(rank) {
    Object.keys(window.statusDHE).each(function(key) {
      if (window.statusDHE[key].rank == rank) {
        window.statusDHE.splice(key, 1);
      }
    });

    $$(".area_dhe_multiple").each(function(td) {
      td.select(".rank_" + rank)[0].remove();
    });
  }

  function deleteSejour() {
    var oForm = getForm("editSejour");
    oForm.del.value = 1;
    oForm.submit();
  }

  function deleteObjects() {
    var oOptions = {
      objName : '{{$op->_view|smarty:nodefaults|escape:"javascript"}}',
      ajax : true
    };

    var oAjaxOptions = {
      onComplete : deleteSejour
    };

    confirmDeletion(getForm("editOp"), oOptions, oAjaxOptions);
  }

  function cancelObjects() {
    cancelOperation();
  //  cancelSejour();
  }

  ProtocoleSelector.init = function(do_not_pop) {
    this.sForSejour     = false;
    this.sChir_id       = "chir_id";
    this.sChir_view     = "chir_id_view";
    this.sCodes_ccam    = "codes_ccam";
    this.sCote          = "cote";
    this.sLibelle       = "libelle";
    this.sTime_op       = "_time_op";
    this.sMateriel      = "materiel";
    this.sExamenPerop   = "exam_per_op";
    this.sExamen        = "examen";
    this.sDepassement   = "depassement";
    this.sForfait       = "forfait";
    this.sFournitures   = "fournitures";
    this.sRques_op      = "rques";
    this.sServiceId{{if $mode_easy === "1col"}}_easy{{/if}} = "service_id";
    this.sPresencePreop = "presence_preop";
    this.sPresencePostop = "presence_postop";
    this.sDureeBioNet   = "duree_bio_nettoyage";
    this.sType          = "type";
    this.sCharge_id     = "charge_id";
    this.sTypeAnesth    = "type_anesth";
    this.sUf_hebergement_id = "uf_hebergement_id";
    this.sUf_medicale_id = "uf_medicale_id";
    this.sUf_soins_id = "uf_soins_id";
    this.sTypesRessourcesIds = "_types_ressources_ids";
    {{if "hidden" !== "dPplanningOp CSejour fields_display show_type_pec"|gconf}}
      this.sTypePec     = "type_pec";
    {{/if}}
    {{if "dPplanningOp CSejour fields_display show_facturable"|gconf}}
      this.sFacturable   = "facturable";
    {{/if}}
    this.sDuree_uscpo   = "duree_uscpo";
    this.sDuree_preop   = "duree_preop";
    this.sDuree_prevu   = "_duree_prevue";
    this.sDuree_prevu_heure   = "_duree_prevue_heure";
    this.sConvalescence = "convalescence";
    this.sDP            = "DP";
    this.sDR            = "DR";
    this.sRques_sej     = "rques";
    this.sExamExtempo   = "exam_extempo";
    this.sHospitDeJour  = "hospit_de_jour";
    this.sProtocoleId   = "protocole_id";
    this.sRRAC   = "RRAC";

    this.sChir_id_easy    = "chir_id";
    this.sServiceId       = "service_id";
    this.sLibelle_easy    = "libelle";
    this.sCodes_ccam_easy = "codes_ccam";
    this.sLibelle_sejour  = "libelle";

    this.sProtoPrescAnesth = "_protocole_prescription_anesth_id";
    this.sProtoPrescChir   = "_protocole_prescription_chir_id";
    this.sProtocole_id     = "protocole_id";
    this._sProtocole_id    = "_protocole_id";
    this.sCodage_CCAM_chir   = '_codage_ccam_chir';
    this.sCodage_CCAM_anesth = '_codage_ccam_anesth';
    this.sCodage_NGAP_sejour = '_codage_ngap';

    this.sPack_appFine_ids = "_pack_appFine_ids";
    this.sDocItems_guid_sejour = "_docitems_guid";
    this.sDocItems_guid_operation = "_docitems_guid";
    this.sHour_entree_prevue = "_hour_entree_prevue";
    this.sMin_entree_prevue = "_min_entree_prevue";
    this.sCircuit_ambu = "circuit_ambu";
    this.sProtocolesOp_ids = "_protocoles_op_ids";

    {{if $protocole_mandatory}}
    this.options.showClose  = false;
    this.options.showReload = false;
    {{/if}}

    if (!do_not_pop) {
      this.pop();
    }
  };

  modeExpertDisplay = function() {
    if( $("modeExpert").style.display == "none") {
      $("modeEasy").hide();
      $("modeExpert").show();
      $("modeExpert-trigger").show();
      $("modeEasy-trigger").hide();
    }
  };

  showKeepProtocol = function(input) {
    if ($V(input)) {
      $('row_keep_protocol_editOpEasy').show();
      $('row_keep_protocol_editOp').show();
    }
    else {
      $('row_keep_protocol_editOpEasy').hide();
      $('row_keep_protocol_editOp').hide();
    }
  };

  createOperation = function(action) {
    if (action == 'recuse') {
      $V(getForm('editSejour').recuse, 0);
    }

    // Vérifier si l'heure de l'intervention est dans les bornes du séjours
    var oFormOperation = getForm("editOp");
    var oFormSejour = getForm("editSejour");

    if (!Operation.checkOperationInStay(oFormOperation, oFormSejour)) {
      return;
    }

    if ($('editOp__keep_protocol').checked) {
      var form = getForm('editOp');
      var postRedirect = 'm=planningOp&{{$dialog|ternary:'a':'tab'}}={{if $op->plageop_id || !$modurgence}}vw_edit_planning{{else}}vw_edit_urgence{{/if}}&operation_id=0&sejour_id=0&protocole_id=' + $V(form._protocole_id){{if $dialog}} + '&dialog=1'{{/if}};
      $V(form.postRedirect, postRedirect);
    }
    submitForms();
  };

  Main.add(function() {
    {{if $protocole_mandatory}}
      ProtocoleSelector.init();
    {{/if}}
  });
</script>

{{mb_include module=planningOp template=js_form_operation}}
{{mb_include module=planningOp template=js_form_sejour}}

<div id="sejour-value-chooser" style="display: none; width: 600px;">

  <div class="small-info text">
    Veuillez indiquer si vous souhaitez garder les valeurs du <strong>dossier existant</strong>
    ou bien utiliser celles que vous venez de saisir (<strong>nouveau dossier</strong>).
  </div>

  <form name="sejourChooserFrm" action="?m={{$m}}" method="get">
  <input name="majDP"     type="hidden" value="0" />
  <input name="majEntree" type="hidden" value="0" />
  <input name="majSortie" type="hidden" value="0" />
  <table class="form">
    <tr>
      <th class="title"></th>
      <th class="category" colspan="2">Dossier existant</th>
      <th class="category" colspan="2">Nouveau dossier</th>
    </tr>
    <tr id="chooseDiag">
      <th>Diagnostic</th>
      <td class="narrow"><input name="valueDiag" type="radio" value="" /></td>
      <td id="chooseNewDiag"></td>
      <td class="narrow"><input name="valueDiag" type="radio" checked="checked" value="" /></td>
      <td id="chooseOldDiag"></td>
    </tr>
    <tr id="chooseAdm">
      <th>Admission</th>
      <td class="narrow"><input name="valueAdm" type="radio" value="" /></td>
      <td id="chooseNewAdm"></td>
      <td class="narrow"><input name="valueAdm" type="radio" checked="checked" value="" /></td>
      <td id="chooseOldAdm"></td>
    </tr>
    <tr id="chooseSortie">
      <th>Sortie prévue</th>
      <td class="narrow"><input name="valueSortie" type="radio" value="" /></td>
      <td id="chooseNewSortie"></td>
      <td class="narrow"><input name="valueSortie" type="radio" checked="checked" value="" /></td>
      <td id="chooseOldSortie"></td>
    </tr>
    <tr>
      <td colspan="5" class="button">
        <button class="tick" type="button" onclick="applyNewSejour()">{{tr}}OK{{/tr}}</button>
      </td>
    </tr>
  </table>
  </form>

</div>

<table class="main me-margin-bottom-40">
  {{if $op->operation_id && !$dialog}}
  <tr>
    <td colspan="2">
      {{assign var=creation_button_label value="COperation.create"}}
      {{if $modurgence}}
        {{assign var=creation_button_label value="`$creation_button_label`_urgence"}}
      {{/if}}
      {{me_button label=$creation_button_label icon=new link="?m=$m&operation_id=0&sejour_id=0"}}
      {{if $patient->_id}}
        {{me_button label="`$creation_button_label`_for_patient" icon=new
          link="?m=$m&operation_id=0&sejour_id=0&pat_id=`$patient->_id`"}}
      {{/if}}
      {{me_dropdown_button button_label=$creation_button_label button_icon=new button_class="me-primary"}}
    </td>
  </tr>
  {{/if}}

  <tr>
    <!-- Création/Modification d'intervention/urgence -->
    <th colspan="2" class="title{{if $modurgence}} urgence{{/if}}{{if $op->_id}} modify{{/if}}">
      <button class="hslip" id="modeEasy-trigger" style="float: right; display:none;" type="button">
        {{tr}}button-COperation-modeEasy{{/tr}}
      </button>
      <button class="hslip" id="modeExpert-trigger" style="float: right; display:none;" type="button">
        {{tr}}button-COperation-modeExpert{{/tr}}
      </button>
      <button id="didac_choose_protocole" style="float:left;" class="search me-primary" type="button" onclick="ProtocoleSelector.init()">
        {{tr}}button-COperation-choixProtocole{{/tr}}
      </button>
      {{mb_ternary var=message test=$op->_id value=modify other=create}}
      {{tr}}COperation-title-{{$message}}{{if $modurgence}}-urgence{{/if}}{{/tr}}
      {{if !$contextual_call}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}}</span>
        {{mb_include module=patients template=inc_icon_bmr_bhre}}
        {{if $chir->_id}}
          - {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
        {{/if}}
      {{else}}
        {{$patient}} {{mb_include module=patients template=inc_icon_bmr_bhre}}
        {{if $chir->_id}}
          - {{$chir}}
        {{/if}}
      {{/if}}
    </th>
  </tr>

  <!-- Mode easy -->
  <tr id="modeEasy" class="halfPane">
    <td class="halfPane">
      {{mb_include template=inc_form_operation_easy}}
    </td>
    <td class="text">
      {{mb_include template=inc_form_sejour_easy}}
    </td>
  </tr>

  <!-- Mode expert -->
  <tr id="modeExpert" style="display:none;">
    <td class="halfPane">
      {{mb_include template=inc_form_operation}}
    </td>
    <td id="inc_form_sejour">
      {{mb_include template=inc_form_sejour mode_operation=true}}
    </td>
  </tr>
  <tr id="me-planning-actions"
      {{if $op->_id && !$op->_ref_sejour->sortie_reelle || $modules.dPbloc->_can->edit || $modules.dPhospi->_can->edit}}
        class="me-bottom-actions"
      {{/if}}
  >
    <td colspan="2" class="button me-padding-8">
    {{if $op->_id}}
      {{if !$op->_ref_sejour->sortie_reelle || $modules.dPbloc->_can->edit || $modules.dPhospi->_can->edit}}
        <button class="submit me-primary" type="button" onclick="submitForms();">{{tr}}Save{{/tr}}</button>
        {{if $op->annulee}}
          <button class="change me-secondary" type="button" onclick="cancelObjects();">{{tr}}Restore{{/tr}}</button>
        {{else}}
          {{if !$op->_ref_sejour->entree_preparee}}
            <button class="tick me-secondary" type="button" onclick="$V(getForm('editSejour').entree_preparee, '1'); submitForms();">{{tr}}CSejour-entree_preparee{{/tr}}</button>
          {{/if}}
          {{if !$conf.dPplanningOp.COperation.cancel_only_for_resp_bloc || $modules.dPbloc->_can->edit || (!$op->_ref_sejour->entree_reelle && !$op->rank)}}
            <button class="cancel me-tertiary" type="button" onclick="cancelObjects();">{{tr}}Cancel{{/tr}}</button>
          {{/if}}
          {{assign var=types_forbidden value=","|explode:"Médecin"}}
          {{if $conf.dPplanningOp.CSejour.use_recuse && "reservation"|module_active && !$app->_ref_user->isFromType($types_forbidden)}}
            {{if $op->_ref_sejour->recuse == "-1"}}
              <button class="tick" onclick="$V(getForm('editSejour').recuse, 0); submitForms();">{{tr}}Validate{{/tr}}</button>
            {{elseif $op->_ref_sejour->recuse == "0"}}
              <button class="cancel me-tertiary" onclick="$V(getForm('editSejour').recuse, -1); submitForms();">Annuler la validation</button>
            {{/if}}
           {{/if}}
        {{/if}}
        {{if !$conf.dPplanningOp.COperation.delete_only_admin || $can->admin}}
        <button class="trash me-tertiary" type="button" onclick="deleteObjects();">{{tr}}Delete{{/tr}}</button>
        {{/if}}

        <button class="print me-tertiary" type="button" onclick="printForm();">{{tr}}Print{{/tr}}</button>
      {{else}}
        <div class="big-info">
          Les informations sur le séjour et sur l'intervention ne peuvent plus être modifiées car <strong>le patient est déjà sorti de l'établissement</strong>.
          Veuillez contacter le <strong>responsable du service d'hospitalisation</strong> pour annuler la sortie ou
          <strong>un administrateur</strong> si vous devez tout de même modifier certaines informations.
        </div>
      {{/if}}
      {{if "reservation"|module_active && $exchange_source->_id}}
        {{mb_include module=reservation template=inc_button_send_mail operation_id=$op->_id}}
      {{/if}}
    {{else}}
      <button id="didac_submit_interv" class="submit me-primary singleclick" type="button" onclick="createOperation();">{{tr}}Create{{/tr}}</button>
      {{assign var=types_forbidden value=","|explode:"Médecin"}}
      {{if $conf.dPplanningOp.CSejour.use_recuse && "reservation"|module_active && !$app->_ref_user->isFromType($types_forbidden)}}
        <button type="button" class="submit singleclick" onclick="createOperation('recuse');">{{tr}}Create{{/tr}} {{tr}}and{{/tr}} {{tr}}Validate{{/tr}}</button>
      {{/if}}
    {{/if}}
    </td>
  </tr>

  {{if $op->_id}}
    <tr>
      <td colspan="2">
        {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$patient object=$op show_send_mail=1}}

        <table class="main tbl me-no-hover">
          <tr>
            <th>{{tr}}Documents{{/tr}}</th>
            {{if "forms"|module_active}}
              <th style="width: 33%;">{{tr}}CExClass|pl{{/tr}}</th>
            {{/if}}
          </tr>
          <tr>
            <td style="vertical-align: top;">
              {{mb_include template=inc_documents_operation operation=$op}}
              <div class="me-padding-10"></div>
              {{mb_include template=inc_files_operation operation=$op}}

              <fieldset style="width: 50%">
                <legend>{{tr}}CDevisCodage{{/tr}}</legend>
                {{mb_script module=ccam script=DevisCodage ajax=1}}
                <script>
                  Main.add(function() {
                    DevisCodage.list('{{$op->_class}}', '{{$op->_id}}');
                  });
                </script>
                <div id="view-devis"></div>
              </fieldset>
            </td>
            {{if "forms"|module_active}}
              <td style="vertical-align: top;">
                {{*{{mb_include module=forms template=inc_widget_ex_class_register object=$op event_name=dhe}}*}}
                {{unique_id var=unique_id_dhe_forms}}

                <script>
                  Main.add(function() {
                    ExObject.loadExObjects("{{$op->_class}}", "{{$op->_id}}", "{{$unique_id_dhe_forms}}", 0.5);
                  });
                </script>

                <div id="{{$unique_id_dhe_forms}}"></div>
              </td>
            {{/if}}
          </tr>
        </table>
      </td>
    </tr>
  {{/if}}
</table>

<!-- Actes -->
{{if $op->_ref_actes|@count}}
<table class="tbl">
  {{mb_include module=cabinet template=inc_list_actes_ccam subject=$op vue=complete}}
</table>
{{/if}}

<!-- la modale qui s'affiche dans le cas où la date de l'intervention est en dehors de celle du séjour -->
<div id="date_alert" style="display:none">
  <div style="text-align:center">
    L'intervention est en dehors du séjour, voulez-vous passer au mode expert pour modifier les dates du séjour?
  </div>
  <div style="text-align:center">
    <button class="tick" onclick="modalWindow.close();modeExpertDisplay();">{{tr}}Yes{{/tr}}</button>
    <button class="cancel" onclick="modalWindow.close();">{{tr}}No{{/tr}}</button>
  </div>
</div>

{{if "appFineClient"|module_active && "appFineClient General block_dhe_no_account"|gconf && $patient->_id && $patient->_ref_appFine_idex && !$patient->_ref_appFine_idex->_id}}
  <script>
    Main.add(function () {
      var button_create_sejour = $('didac_button_create') ? $('didac_button_create') : $('didac_submit_interv');

      if (button_create_sejour) {
        {{if $patient->email}}
          button_create_sejour.disabled = true;
          button_create_sejour.title = $T('CAppFineClient-msg-Please create an AppFine account before creating this stay');
        {{else}}
          button_create_sejour.disabled = false;
          button_create_sejour.title = '';
        {{/if}}
      }
    });
  </script>
{{/if}}

<form name="annulation_sejour_operation">
  {{mb_include module=dPplanningOp template=inc_form_sejour_annule}}
</form>

