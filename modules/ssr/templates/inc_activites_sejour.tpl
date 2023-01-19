{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=seance_collective ajax=1}}
{{mb_script module=ssr script=csarr             register=true}}
{{mb_script module=ssr script=evt_ssr           ajax=1}}
{{mb_script module=ssr script=planification     ajax=1}}

{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

<script>
  selectActivite = function(activite, use_acte_presta) {
    $V(oFormEvenementSSR.prescription_line_element_id, '');
    $V(oFormEvenementSSR._element_id, '');

    $$("button.activite").invoke("removeClassName", "selected");
    $("trigger-"+activite).addClassName("selected");

    $$("div.activite").invoke("hide");
    if (activite !== "") {
      $("activite-"+activite).show();
    }

    // On masque les techncien et on enleve le technicien selectionné
    $$("div.techniciens").invoke("hide").invoke("removeClassName", "selected");
    $$("button.ressource").invoke("removeClassName", "selected");
    $V(oFormEvenementSSR.therapeute_id, '');

    // Suppression des valeurs du select de technicien
    $$("select._technicien_id").each(function(select_tech){
      $V(select_tech, '');
    });

    // Affichage des techniciens correspondants à l'activité selectionnée
    $("techniciens-"+activite).show();
    $("techniciens2-"+activite).show();
    $("techniciens3-"+activite).show();

    if (use_acte_presta == 'csarr') {
      $('div_other_csarr').hide();
      $('other_csarr').hide();
      $V(oFormEvenementSSR.code_csarr, '');
      $$(".codes-csarr").invoke('hide');
    }
    else if (use_acte_presta == 'presta'){
      // Presta
      $('div_other_presta_ssr').hide();
      $('other_presta_ssr').hide();
      $V(oFormEvenementSSR.code_presta_ssr, '');
      $$(".code_presta_ssr").invoke('hide');
    }

    // Mise en evidence des elements dans les plannings
    addBorderEvent();
    refreshSelectSeances();
  };

  selectElement = function(line_id, use_acte_presta){
    $V(oFormEvenementSSR.line_id, line_id);

    $$("button.line").invoke("removeClassName", "selected");
    if (line_id !== '') {
      $("line-"+line_id).addClassName("selected");
    }

    if (use_acte_presta == 'csarr') {
      $$(".codes-csarr").invoke('hide');
      if (line_id !== '') {
        $("csarrs-"+line_id).show();
      }
      $('div_other_csarr').show();
    }
    else if (use_acte_presta == 'presta'){
      // Presta
      $$(".prestas-ssr").invoke('hide');
      if (line_id !== '') {
        $("prestas-ssr-"+line_id).show();
      }
      $('div_other_presta_ssr').show();
    }

    // Deselection de tous les codes
    Planification.removeCodes(oFormEvenementSSR);
    $V(oFormEvenementSSR._sejours_guids, '');

    // Mise en evidence des elements dans les plannings
    addBorderEvent();
    refreshSelectSeances();
  };

  selectTechnicien = function(kine_id, num, buttonSelected) {
    if (num == '3') {
      $V(oFormEvenementSSR.therapeute3_id, kine_id);
    }
    else if(num == '2') {
      $V(oFormEvenementSSR.therapeute2_id, kine_id);
    }
    else {
      $V(oFormEvenementSSR.therapeute_id, kine_id);
    }

    if(buttonSelected){
      $$("button.ressource").invoke("removeClassName", "selected");
      buttonSelected.addClassName("selected");
    }
    if (!num) {
      PlanningTechnicien.show(kine_id, null, '{{$bilan->sejour_id}}');
      if($V(oFormEvenementSSR.equipement_id)){
        PlanningEquipement.show($V(oFormEvenementSSR.equipement_id), '{{$bilan->sejour_id}}');
      }
      refreshSelectSeances();
    }
  };

  selectEquipement = function(equipement_id) {
    $V(oFormEvenementSSR.equipement_id, equipement_id);
    $$("button.equipement").invoke("removeClassName", "selected");
    if ($("equipement-"+equipement_id)){
      $("equipement-"+equipement_id).addClassName("selected");
    }

    if (equipement_id) {
      PlanningEquipement.show(equipement_id,'{{$bilan->sejour_id}}');
    }
    else {
      PlanningEquipement.hide();
    }
    refreshSelectSeances();
  };

  refreshSelectSeances = function(){
    if ($V(oFormEvenementSSR.type_seance) == 'collective') {
      $('niveau_ssr_msg').hide();
      $('patient_missing_line').hide();
      $('niveau_ssr_msg').set('can-be-displayed', '0');
      $V(oFormEvenementSSR.patient_missing, '0');
    }
    else {
      $V(oFormEvenementSSR.seance_collective_id, '');
      $('niveau_ssr_msg').set('can-be-displayed', '1');
      $('niveau_ssr_msg').show();
      $('patient_missing_line').show();
    }
    if($V(oFormEvenementSSR.therapeute_id) &&
       $V(oFormEvenementSSR.line_id) &&
       $V(oFormEvenementSSR.type_seance) == 'collective'){

      var url = new Url("ssr", "ajax_vw_select_seances");
      url.addParam("therapeute_id", $V(oFormEvenementSSR.therapeute_id));
      url.addParam("equipement_id", $V(oFormEvenementSSR.equipement_id));
      url.addParam("prescription_line_element_id", $V(oFormEvenementSSR.line_id));
      url.requestUpdate("select-seances", {
        onComplete: function(){
          $('seances').show();
          oFormEvenementSSR.seance_collective_id.show();
        }
      });
    } else {
      $('seances').hide();
      $V(oFormEvenementSSR.seance_collective_id, '');
    }
    var button_add_patient = $('seance_collective_add_patient');
    if ($V(oFormEvenementSSR.type_seance) == 'collective' && $V(oFormEvenementSSR._element_id)) {
      button_add_patient.show();
      if (!$V(oFormEvenementSSR._sejours_guids)) {
        button_add_patient.className = "add";
        button_add_patient.innerHTML = $T('CEvenementSSR-seance_collective_id-add_patients');
      }
    }
    else {
      button_add_patient.hide();
    }

  };

  hideCodes = function(use_acte_presta) {
    // Deselection des codes csarrs
    if (use_acte_presta == 'csarr') {
      $V(oFormEvenementSSR._csarr, false);
      $$('#other_csarr span').invoke('remove');
      $('other_csarr').hide();
    }
    else if (use_acte_presta == 'presta'){
      // Presta
      $V(oFormEvenementSSR._presta_ssr, false);
      $$('#other_presta_ssr span').invoke('remove');
      $('other_presta_ssr').hide();
    }
  };

  submitSSR = function(use_acte_presta){
    var patient_missing = $V(oFormEvenementSSR.patient_missing) === '1';
    // Test de la presence d'au moins un code SSR
    if (use_acte_presta == 'csarr') {
      var csarr_count = $V(oFormEvenementSSR.code_csarr) ? 1 : 0;
      csarr_count += oFormEvenementSSR.select('input.checkbox-csarrs:checked').length;
      csarr_count += oFormEvenementSSR.select('input.checkbox-other-csarrs').length;

      if (csarr_count == 0 && !patient_missing) {
        alert($T('ssr-to_selected-Csarr'));
        return false;
      }
    }
    else if (use_acte_presta == 'presta'){
      // Presta
      var other_presta = oFormEvenementSSR.select('input.checkbox-other-prestas_ssr').length;
      var presta_ssr_count = ($V(oFormEvenementSSR.code_presta_ssr) && other_presta) ? 1 : 0;
      presta_ssr_count += oFormEvenementSSR.select('input.checkbox-prestas_ssr:checked').length;
      presta_ssr_count += other_presta;

      if (presta_ssr_count == 0 && !oFormEvenementSSR.no_code_to_evt.checked && !patient_missing) {
        alert($T('CPrestaSSR-msg-Please select at least one SSR service'));
        return false;
      }
    }
    else if (use_acte_presta == 'aucun' && !patient_missing &&
      oFormEvenementSSR.select('input[type=radio][name=prescription_line_element_id]:checked').length < 1) {
      alert($T('ssr-no_element_for_evt'));
      return false;
    }

    if($V(oFormEvenementSSR.type_seance) != 'collective' || ($V(oFormEvenementSSR.type_seance) == 'collective' && !$V(oFormEvenementSSR.seance_collective_id))){
      if((oFormEvenementSSR.select('input.days:checked').length == 0)){
        alert($T('ssr-to_selected-min_a_day'));
        return false;
      }
      if(!$V(oFormEvenementSSR._heure_deb)){
        alert($T('ssr-to_selected-hour'));
        return false;
      }
      if(!$V(oFormEvenementSSR.duree)){
        alert($T('ssr-to_selected-duree'));
        return false;
      }
    }

    if (oFormEvenementSSR.equipement_id) {
      if(!oFormEvenementSSR.select("button.equipement.selected").length && !$V(oFormEvenementSSR.equipement_id)){
        alert($T('ssr-to_selected-equipement'));
        return false;
      }
    }
    $V(oFormEvenementSSR._type_seance, $V(oFormEvenementSSR.type_seance));

    return onSubmitFormAjax(oFormEvenementSSR, { onComplete: function(){
      refreshPlanningsSSR();
      $$(".days").each(function(e){
        $V(e, '');
      });

      // Suppression des actes cdarrs selectionnés
      $V(oFormEvenementSSR._heure_deb, '');
      $V(oFormEvenementSSR._heure_deb_da, '');
      $V(oFormEvenementSSR._heure_fin, '');
      $V(oFormEvenementSSR._heure_fin_da, '');
      $V(oFormEvenementSSR.duree, $V(oFormEvenementSSR._default_duree));
      $V(oFormEvenementSSR.seance_collective_id, '');
      $V(oFormEvenementSSR._sejours_guids, '');
      $V(oFormEvenementSSR.type_seance, 'dediee');
      $$("input[name='type_seance']").each(function(input){
        input.disabled = false;
      });
      if(oFormEvenementSSR.seance_collective_id){
        oFormEvenementSSR.seance_collective_id.hide();
      }

      hideCodes('{{$use_acte_presta}}');

      refreshCountUsageElt($V(oFormEvenementSSR.line_id));

      selectElement($V(oFormEvenementSSR.line_id));
      if (oFormEvenementSSR.no_code_to_evt) {
        $V(oFormEvenementSSR.no_code_to_evt, 0);
      }
    }} );
  };

  refreshPlanningsSSR = function(){
    Planification.refreshSejour('{{$bilan->sejour_id}}', true);
    PlanningTechnicien.show($V(oFormEvenementSSR.therapeute_id), null, '{{$bilan->sejour_id}}');
    if($V(oFormEvenementSSR.equipement_id)){
      PlanningEquipement.show($V(oFormEvenementSSR.equipement_id),'{{$bilan->sejour_id}}');
    }
  };

  addBorderEvent = function(){
    // Classe des evenements à selectionner
    var category_id = $V(oFormEvenementSSR._category_id);
    var element_id  = $V(oFormEvenementSSR._element_id);
    var eventClass = (element_id) ? ".CElementPrescription-"+element_id : ".CCategoryPrescription-"+category_id;
    var planning = $('planning-sejour');

    // On ne passe pas en selected les evenements qui possedent la classe tag_cat
    if(element_id){
      var elements_tag = planning.select(".event.elt_selected"+eventClass+":not(.tag_cat)");
      if (planning.select(".event.elt_selected"+eventClass+".selected:not(.tag_cat)").length) {
        elements_tag.invoke("removeClassName", 'selected');
      }
      else {
        elements_tag.invoke("addClassName", 'selected');
      }
    }
    else {
      var elements = planning.select(".event.elt_selected"+eventClass);
      if (planning.select(".event.elt_selected"+eventClass+".selected").length) {
        elements.invoke("removeClassName", 'selected');
      }
      else {
        elements.invoke("addClassName", 'selected');
      }
    }

    planning.select(".event.elt_selected:not("+eventClass+")").invoke("removeClassName", 'selected');

    // Selection de tous les elements qui ont la classe spécifiée
    planning.select(".event"+eventClass).invoke("addClassName", 'elt_selected');

    // Deselection de tous les elements deja selectionnés qui n'ont pas la bonne classe
    planning.select(".event:not("+eventClass+")").invoke("removeClassName", 'elt_selected');

    // Suppression de la classe tag_cat de tous les evenements selectionnés
    planning.select(".event"+eventClass).invoke("removeClassName", 'tag_cat');

    // Si la selection a eu lieu suite au choix d'une categorie, ajout d'une classe aux evenements
    if(!element_id){
      planning.select(".event.elt_selected"+eventClass).invoke("addClassName", 'tag_cat');
    }

    // Parfois le planning n'est pas prêt

    if (planning.down('div.planning')) {
      window["planning-"+planning.down('div.planning').id].updateNbSelectEvents();
    }
  };

  updateModalSsr = function(){
    var oFormEvents = getForm("form_list_ssr");
    var url = new Url("ssr", "ajax_update_modal_evts_modif");
    url.addParam("token_evts", $V(oFormEvents.token_evts));
    url.requestModal("40%", "60%");
    modalWindow = url.modalObject;
  };

  onchangeSeance = function(seance_id){
    $('date-evenements').setVisible(!seance_id);
  };

  toggleAllDays = function(){
    var days = oFormEvenementSSR.select('input.days');
    days.slice(0,5).each(function(e){
        e.checked = true;
    });
    days.slice(5,7).each(function(e){
      e.checked = false;
  });
  };

  refreshCountUsageElt = function(line_elt_id) {
    var span = $("count_usage_" + line_elt_id);

    if (!span) {
      return;
    }

    new Url("ssr", "ajax_count_usage_elt", "raw")
      .addParam("line_elt_id", line_elt_id)
      .requestJSON(function(result) {
        span.update("(" + result + ")")
      });
  };

  refreshAllCounts = function() {
    {{foreach from=$prescription->_ref_prescription_lines_element_by_cat item=_lines_by_chap}}
      {{foreach from=$_lines_by_chap item=_lines_by_cat}}
        {{foreach from=$_lines_by_cat.element item=_line}}
          refreshCountUsageElt({{$_line->_id}});
        {{/foreach}}
      {{/foreach}}
    {{/foreach}}
  };

  toggleEmptyCat= function(checkbox) {
    var checkboxEnabled = $V(checkbox) === '1';
    $('trigger-')[checkboxEnabled ? 'show' : 'hide']();
    if (checkboxEnabled) {
      $$('.cancel.equipement').invoke('click');
      return;
    }
    var emptyCatSelected = $('trigger-').hasClassName('selected');
    if (!emptyCatSelected) {
      return;
    }
    $('trigger-').removeClassName('selected');
    $('techniciens-').hide();
    hideCodes('{{$use_acte_presta}}');

    $$('.prestas-ssr').invoke('hide');
    if ($('div_other_presta_ssr')) {
      $('div_other_presta_ssr').hide();
    }
    $$('.codes-csarr').invoke('hide');
    if ($('div_other_csarr')) {
      $('div_other_csarr').hide();
    }
  };

  var oFormEvenementSSR;
  Main.add(function(){
    oFormEvenementSSR = getForm("editEvenementSSR");
    window.toCheck = false;

    // CsARR other code autocomplete
    if ($('code_csarr_autocomplete')) {
      var url = new Url("ssr", "httpreq_do_csarr_autocomplete");
      url.autoComplete(oFormEvenementSSR.code_csarr, "code_csarr_autocomplete", {
        dropdown: true,
        minChars: 2,
        select: "value",
        callback: function(input, queryString){
          return (queryString + "&type_seance="+$V(oFormEvenementSSR.type_seance));
        },
        updateElement: function(selected) {
          Planification.updateFieldCodesSSR(selected, 'csarr');
        }
      } );
    }

    // Presta SSR other code autocomplete
    if ($('code_presta_ssr_autocomplete')) {
      var url = new Url("ssr", "ajax_presta_ssr_autocomplete");
      url.addParam("code", $V(oFormEvenementSSR.code_presta_ssr));
      url.autoComplete(oFormEvenementSSR.code_presta_ssr, "code_presta_ssr_autocomplete", {
        dropdown: true,
        minChars: 2,
        method: "get",
        select: "value",
        updateElement: function(selected) {
          Planification.updateFieldCodesSSR(selected, 'presta_ssr');
        }
      } );
    }

    // Initialisation du timePicker
    Control.Tabs.create('tabs-activites', true);

    {{if $selected_cat}}
      selectActivite('{{$selected_cat->_guid}}', '{{$use_acte_presta}}');
      $("technicien-{{$selected_cat->_id}}-{{$user->_id}}").onclick();
    {{/if}}

    {{if !$prescription}}
      $('div_other_csarr').show();
    {{/if}}
    {{if $current_m == "psy"}}
      $('editEvenementSSR_type_seance_non_dediee').hide();
      $('labelFor_editEvenementSSR_type_seance_non_dediee').hide();
    {{/if}}
    {{if $current_m == "psy" && !"ssr general see_collective_planif_psy"|gconf}}
      $('editEvenementSSR_type_seance_collective').hide();
      $('labelFor_editEvenementSSR_type_seance_collective').hide();
    {{/if}}
  });
</script>

{{if !$bilan->technicien_id}}
<div class="small-warning">
  {{tr}}ssr-patient_no_technicien{{/tr}}
  <a class="button search" href="?&m={{$current_m}}&tab=vw_idx_repartition">
    {{tr}}ssr-go_to_repartition{{/tr}}
  </a>
</div>
{{/if}}

<ul id="tabs-activites" class="control_tabs small" style="height: 20px;">
  <li>
    <a href="#add_ssr">{{tr}}Activities{{/tr}}</a>
  </li>
  <li>
    <a href="#outils">{{tr}}Tools{{/tr}}</a>
  </li>
  <button class="print" onclick="Seance.eventsSejour('{{$sejour->_id}}');" style="float:right;margin-top: -1px;">{{tr}}mod-ssr-tab-vw_list_events_sejour{{/tr}}</button>
</ul>

<form name="editSejourHospitDeJour" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$sejour}}
  {{mb_key object=$sejour}}
  {{mb_field object=$sejour field=hospit_de_jour hidden=1}}
</form>

<div id="add_ssr">
  <!-- Modification du bilan SSR, brancardage -->
  <form name="editBilanSSR" method="post" onsubmit="return onSubmitFormAjax(this);">
    <input type="hidden" name="m" value="ssr" />
    <input type="hidden" name="dosql" value="do_bilan_ssr_aed" />
    <input type="hidden" name="del" value="0" />
    {{mb_key object=$bilan}}
    {{mb_key object=$sejour}}

    <table class="form me-no-align me-no-box-shadow me-no-align">
      <tr>
        <th>{{mb_label object=$sejour field=hospit_de_jour}}</th>
        <td>
          <div id="demi-journees" style="float: right; {{if !$sejour->hospit_de_jour}}display: none;{{/if}}">
            {{mb_field object=$bilan field=demi_journee_1 onchange="this.form.onsubmit();" typeEnum=checkbox}}
            {{mb_label object=$bilan field=demi_journee_1}}
            {{mb_field object=$bilan field=demi_journee_2 onchange="this.form.onsubmit();" typeEnum=checkbox}}
            {{mb_label object=$bilan field=demi_journee_2}}
          </div>
          <script>
            updateDemiJournees = function (input) {
              $('demi-journees').setVisible($V(input) == '1');
              var form = getForm("editSejourHospitDeJour");
              $V(form.hospit_de_jour, $V(input));
              form.onsubmit();
            }
          </script>
          {{mb_field object=$sejour field=hospit_de_jour onchange="updateDemiJournees(this);"}}
        </td>
      </tr>

      <tr>
        <th style="width: 94px">{{mb_label object=$bilan field=entree}}</th>
        <td>
          {{if $current_m == "ssr"}}
            <div style="float: right;">
              {{mb_field object=$bilan field=brancardage onchange="this.form.onsubmit();" typeEnum=checkbox}}
              {{mb_label object=$bilan field=brancardage}}
            </div>
          {{/if}}
          {{mb_value object=$bilan field=entree}}
        </td>
      </tr>
      <tr>
        <th>{{tr}}CBilan{{$m|strtoupper}}-technicien_id{{/tr}}</th>
        <td><strong>{{mb_value object=$bilan field=technicien_id}}</strong></td>
      </tr>
    </table>
  </form>

  <form name="editEvenementSSR" method="post" action="?" onsubmit="return submitSSR('{{$use_acte_presta}}');">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="dosql" value="do_evenement_ssr_multi_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="sejour_id" value="{{$bilan->sejour_id}}">
    {{mb_field hidden=true object=$evenement field=therapeute_id prop="ref notNull"}}
    {{mb_field hidden=true object=$evenement field=therapeute2_id}}
    {{mb_field hidden=true object=$evenement field=therapeute3_id}}
    <input type="hidden" name="line_id" value="" />
    <input type="hidden" name="_element_id" value="" />
    <input type="hidden" name="_category_id" value="" />
    <input type="hidden" name="_type_seance" value="" />
    <input type="hidden" name="_sejours_guids" value="" />

    <table class="form me-no-align me-no-box-shadow me-small-form">
      <tr>
        <th>{{mb_label object=$evenement field=type_seance}}</th>
        <td>
          {{mb_field object=$evenement field=type_seance type=checkbox typeEnum=radio onchange="refreshSelectSeances();" onclick="Planification.filterCodesCsARR(this.form);"}}
        </td>
      </tr>

      <tr id="patient_missing_line">
        <th>{{mb_label object=$evenement field=patient_missing}}</th>
        <td>
          {{mb_field object=$evenement field=patient_missing onchange="toggleEmptyCat(this);"}}
        </td>
      </tr>

      {{if $prescription}}
      <tr>
        <th style="width: 94px">{{tr}}CCategoryPrescription|pl{{/tr}}</th>
        <td class="text">
          {{foreach from=$prescription->_ref_prescription_lines_element_by_cat item=_lines_by_chap}}
            {{foreach from=$_lines_by_chap item=_lines_by_cat}}
              {{foreach from=$_lines_by_cat.element item=_line name=category}}
                {{if $smarty.foreach.category.first}}
                  {{assign var=category value=$_line->_ref_element_prescription->_ref_category_prescription}}
                  <button id="trigger-{{$category->_guid}}" class="none activite" type="button"
                          onclick="
                            $V(this.form._category_id, '{{$category->_id}}');
                            selectActivite('{{$category->_guid}}');
                            $('niveau_ssr_msg').hide();">
                    {{$category}}
                  </button>
                {{/if}}
              {{/foreach}}
            {{/foreach}}
          {{/foreach}}
          <button id="trigger-" class="none activite" type="button" style="display: none"
                  onclick="
                    selectActivite('');
                    selectElement('', '{{$use_acte_presta}}');
                    hideCodes('{{$use_acte_presta}}');
                    ">
            {{tr}}CCategoryPrescription.none{{/tr}}
          </button>
        </td>
      </tr>

      <tr>
        <th>{{tr}}ssr-element|pl{{/tr}}</th>
        <td class="text">
          {{foreach from=$lines_by_element item=_lines_by_chap}}
            {{foreach from=$_lines_by_chap item=_lines_by_cat}}
              {{foreach from=$_lines_by_cat item=_lines_by_elt name=category}}
                {{foreach from=$_lines_by_elt item=_line name=elts}}
                  {{assign var=element value=$_line->_ref_element_prescription}}
                  {{if $smarty.foreach.category.first &&  $smarty.foreach.elts.first}}
                    {{assign var=category value=$element->_ref_category_prescription}}
                    <div class="activite" id="activite-{{$category->_guid}}" style="display: none;">
                  {{/if}}

                  {{if $smarty.foreach.elts.first && $_lines_by_elt|@count > 1}}
                   <span class="mediuser" style="font-weight: bold; border-left-color: #{{$element->_color}};"
                          onmouseover="ObjectTooltip.createEx(this, '{{$element->_guid}}')">
                    {{$element}}
                   </span>

                   <span id="count_usage_{{$_line->_id}}">({{$_line->_count_usage_elt}})</span>
                   <br />
                  {{/if}}

                  <span style="float: right">
                    {{if $_line->date_arret && $_line->time_arret}}
                      <i class="me-icon cross-circle me-error"
                         title="Arrêt : {{mb_value object=$_line field=date_arret}}{{if $_line->time_arret}} à {{mb_value object=$_line field=time_arret}}{{/if}}"></i>
                    {{/if}}
                    {{assign var=to value=$_line->date_arret}}
                    {{if !$to}}
                      {{assign var=to value=$_line->_fin_reelle}}
                    {{/if}}
                    {{mb_include module=system template=inc_opened_interval_date from=$_line->debut}}
                  </span>

                  <label>
                    <div>
                      {{mb_include module=ssr template=vw_line_alerte_ssr line=$_line include_form=0 name_form="activite" see_alertes=0}}
                    </div>

                    <input type="radio" name="prescription_line_element_id" id="line-{{$_line->_id}}" class="search line"
                           onclick="
                            $V(this.form._element_id, '{{$_line->element_prescription_id}}');
                            selectElement('{{$_line->_id}}', '{{$use_acte_presta}}');
                            hideCodes('{{$use_acte_presta}}');
                            Evt_SSR.showSSRPrioriteMsg('{{$_line->_ref_element_prescription->_niveau_ssr}}', $('niveau_ssr_msg'))" />

                    {{if $_lines_by_elt|@count == 1}}
                     <span class="mediuser" style="font-weight: bold; border-left-color: #{{$element->_color}};"
                            onmouseover="ObjectTooltip.createEx(this, '{{$element->_guid}}')">
                      {{$element}}
                     </span>
                     <span id="count_usage_{{$_line->_id}}">({{$_line->_count_usage_elt}})</span>
                    {{/if}}

                    {{if $_line->commentaire}}
                      {{$_line->commentaire}}
                    {{/if}}
                  </label>
                  <br style="clear: both;"/>
                  {{if $smarty.foreach.category.last &&  $smarty.foreach.elts.last}}
                    </div>
                  {{/if}}
                {{/foreach}}
              {{/foreach}}
            {{/foreach}}
          {{/foreach}}
        </td>
      </tr>
      <tr>
        <td colspan="2" id="niveau_ssr_msg" data-can-be-displayed="1" style="display: none">
        </td>
      </tr>
      {{/if}}

      {{if $use_acte_presta == "csarr"}}
        <tr id='tr-csarrs'>
          <th>{{tr}}CActeCsARR|pl{{/tr}}</th>
          <td class="text">
            <button type="button" class="add me-tertiary" onclick="$('remarque_ssr').toggle(); this.form.remarque.focus();"
                    style="float: right">
              {{mb_label class=CEvenementSSR field=remarque}}
            </button>

            {{if $prescription}}
              <!-- Affichage des codes csarrs -->
              {{foreach from=$prescription->_ref_prescription_lines_element_by_cat item=_lines_by_chap}}
                {{foreach from=$_lines_by_chap item=_lines_by_cat}}
                  {{foreach from=$_lines_by_cat.element item=_line}}
                    <div id="csarrs-{{$_line->_id}}" style="display : none;" class="codes-csarr">
                      {{foreach from=$_line->_ref_element_prescription->_ref_csarrs item=_csarr}}
                        <label class="label-csarrs" {{if $_csarr->type_seance && $_csarr->type_seance != "dediee"}}style="display : none;"{{/if}}>
                          <input type="checkbox" class="checkbox-csarrs nocheck" name="csarrs[{{$_csarr->code}}]" data-duree="{{$_csarr->duree}}"
                                 data-type_seance="{{$_csarr->type_seance}}"
                                 value="{{$_csarr->code}}" onchange="Planification.countCodesCsarr(this.form); Planification.calculateDuration(form);"/>
                          <span onmouseover="ObjectTooltip.createEx(this, '{{$_csarr->_guid}}')">
                            {{$_csarr->code}}
                          </span>
                        </label>
                        {{/foreach}}
                    </div>
                  {{/foreach}}
                {{/foreach}}
              {{/foreach}}
            {{/if}}

            <!-- Autre code CsARR -->
            <div id="div_other_csarr" style="display: none;">
              <label>
                <input type="checkbox" name="_csarr" value="other" onclick="Planification.toggleOtherCsarr(this);" /> {{tr}}Other{{/tr}}:
              </label>
              <span id="other_csarr" style="display: block;">
                 <input type="text" name="code_csarr" class="autocomplete" canNull=true size="2" />
                 <button type="button" class="search notext" onclick="CsARR.viewSearch(function(code) {Planification.updateFieldCodesSSR(code, 'csarr');}{{if $prescription && $prescription->praticien_id}}, '{{$prescription->praticien_id}}'{{/if}});">
                   {{tr}}CActiviteCsARR-action-search{{/tr}}
                 </button>
                 <div style="display: none;" class="autocomplete" id="code_csarr_autocomplete"></div>
              </span>
            </div>
          </td>
        </tr>
      {{/if}}

      <!-- Affichage des prestations SSR -->
      {{if $use_acte_presta == "presta"}}
      <!-- prestation SSR -->
        <tr id='tr-presta-ssr'>
          <th>{{tr}}CPrestaSSR{{/tr}}</th>
          <td class="text">
            <div style="float: right;">
              <label for="editEvenementSSR_no_code_to_evt">
                <input type="checkbox" class="checkbox-no_code_to_evt" name="no_code_to_evt" onclick="Evt_SSR.cleanCodes(this.form)"/>
                {{tr}}CEvenementSSR-no_code{{/tr}}
              </label>
              <br/>
              <button type="button" class="add me-secondary" onclick="$('remarque_ssr').toggle(); this.form.remarque.focus();">
                {{mb_label class=CEvenementSSR field=remarque}}
              </button>
            </div>
            <div style="float: left;">
              {{if $prescription}}
                <!-- Affichage des prestations SSR -->
                {{foreach from=$prescription->_ref_prescription_lines_element_by_cat item=_lines_by_chap}}
                  {{foreach from=$_lines_by_chap item=_lines_by_cat}}
                    {{foreach from=$_lines_by_cat.element item=_line}}
                      <div id="prestas-ssr-{{$_line->_id}}" style="display : none;" class="prestas-ssr">
                        {{foreach from=$_line->_ref_element_prescription->_refs_presta_ssr item=_presta}}
                          {{unique_id var=acte_id}}
                          <label>
                            <input type="checkbox" class="checkbox-prestas_ssr nocheck" value="{{$_presta->code}}"
                                   name="prestas_ssr[{{$_presta->code}}-{{$acte_id}}]" />
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$_presta->_guid}}')">
                              {{$_presta->code}}
                            </span>
                          </label>

                          (<span title="{{tr}}CActePrestationSSR-Amount of code to add{{/tr}}">x</span>
                          <input type="text" id="prestas_ssr_quantity_{{$_presta->code}}_{{$acte_id}}"
                                 name="prestas_ssr_quantity[{{$_presta->code}}-{{$acte_id}}]" value="{{$_presta->quantite}}" style="width: 17px;" />)

                          <script>
                            Main.add(function () {
                              $('prestas_ssr_quantity_{{$_presta->code}}_{{$acte_id}}').addSpinner({min: 1});
                            });
                          </script>
                        {{/foreach}}
                      </div>
                    {{/foreach}}
                  {{/foreach}}
                {{/foreach}}
              {{/if}}

              <!-- Autre prestations SSR -->
              <div id="div_other_presta_ssr" style="display: none;">
                <label>
                  <input type="checkbox" name="_presta_ssr" value="other" onclick="Planification.toggleOtherPresta(this);" /> {{tr}}Other{{/tr}}:
                </label>
                <span id="other_presta_ssr" style="display: block;">
                   <input type="text" name="code_presta_ssr" class="autocomplete" canNull=true size="2" />
                   <div style="display: none;" class="autocomplete" id="code_presta_ssr_autocomplete"></div>
                </span>
              </div>
            </div>
          </td>
        </tr>
      {{/if}}

      <tr id="remarque_ssr" style="display: none;">
        <th>{{mb_label object=$evenement field=remarque}}</th>
        <td>{{mb_field object=$evenement field=remarque}}</td>
      </tr>

      <tr>
        <th>
          <button type="button" onclick="Evt_SSR.toggleOtherReeducs(this);" title="{{tr}}Add{{/tr}}"
                  class="notext me-tertiary {{if $evenement->therapeute2_id || $evenement->therapeute3_id}}up{{else}}down{{/if}}"></button>
          {{tr}}CEvenement{{$m|strtoupper}}-therapeute_id{{/tr}}
        </th>
        <td class="text">
          {{mb_include module=ssr template=inc_select_therapeute_seance num="" none_list=$reeducateurs}}
        </td>
      </tr>

      <tr class="other_reeduc" {{if !$evenement->therapeute2_id && !$evenement->therapeute3_id}}style="display: none"{{/if}}>
        <th>{{tr}}CEvenement{{$m|strtoupper}}-therapeute2_id{{/tr}}</th>
        <td>
          {{mb_include module=ssr template=inc_select_therapeute_seance num="2" none_list=$reeducateurs}}
        </td>
      </tr>
      <tr class="other_reeduc" {{if !$evenement->therapeute2_id && !$evenement->therapeute3_id}}style="display: none"{{/if}}>
        <th>{{tr}}CEvenement{{$m|strtoupper}}-therapeute3_id{{/tr}}</th>
        <td>
          {{mb_include module=ssr template=inc_select_therapeute_seance num="3" none_list=$reeducateurs}}
        </td>
      </tr>

      {{if $app->user_prefs.ssr_planification_show_equipement}}
      <tr>
        <th>
          {{mb_label object=$evenement field=equipement_id}}
          {{mb_field object=$evenement field=equipement_id hidden=true}}
        </th>
        <td class="text">
          {{foreach from=$plateau->_ref_equipements item=_equipement}}
          <button id="equipement-{{$_equipement->_id}}" class="none equipement" type="button" onclick="$V(getForm('editEvenementSSR')._equipement_id, ''); selectEquipement('{{$_equipement->_id}}');">
            {{$_equipement}}
          </button>
          {{/foreach}}
          <button id="equipement-" type="button" class="cancel equipement me-tertiary" onclick="$V(getForm('editEvenementSSR')._equipement_id, ''); selectEquipement(''); ">{{tr}}None{{/tr}}</button>

          <select name="_equipement_id" onchange="selectEquipement(this.value);" style="width: 6em;">
            <option value="">&mdash; {{tr}}Other{{/tr}}</option>
            {{foreach from=$plateaux item=_plateau}}
              {{if $_plateau->_id != $plateau->_id}}
                <optgroup label="{{$_plateau->_view}}">
                {{foreach from=$_plateau->_ref_equipements item=_equipement}}
                  <option value="{{$_equipement->_id}}">{{$_equipement->_view}}</option>
                {{/foreach}}
                </optgroup>
              {{/if}}
            {{/foreach}}
          </select>
        </td>
      </tr>
      {{/if}}

      <tr id="seances" style="display: none;">
        <th>{{mb_label object=$evenement field="seance_collective_id"}}</th>
        <td id="select-seances"></td>
      </tr>

      <tbody id="date-evenements">
        <tr>
          <th style="vertical-align: middle;">{{tr}}Day{{/tr}}</th>
          <td style="text-align: center;">
            <table>
              <tr>
                {{foreach from=$week_days key=_number item=_day}}
                  <td class="me-padding-left-4 me-padding-right-4">
                    <label>
                      {{$_day}}<br />
                      <input class="days nocheck" type="checkbox" name="_days[{{$_number}}]" value="{{$_number}}"
                             onclick="Planification.checkPlanificationPatient(this.form);"/>
                    </label>
                  </td>
                {{/foreach}}
                <td style="padding-left: 3em; text-align: center;">
                  <label style="float: right;">
                    <button type="button" class="me-tertiary" onclick="toggleAllDays();">{{tr}}Week{{/tr}}</button>
                  </label>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$evenement field=_heure_deb}} /
            {{mb_label object=$evenement field=duree}} /
            {{mb_label object=$evenement field=_heure_fin}}
          </th>
          <td>
            <script>
              updateDuree = function(form) {
                if ($V(form._heure_deb) && $V(form._heure_fin)) {
                  var timeDeb = Date.fromDATETIME("2001-01-01 " + $V(form._heure_deb));
                  var timeFin = Date.fromDATETIME("2001-01-01 " + $V(form._heure_fin));
                  $V(form.duree, (timeFin-timeDeb) / Date.minute, false);
                }

                if (!$V(form._heure_fin)) {
                  updateHeureFin(form);
                }
                else {
                  Planification.checkPlanificationPatient(form);
                }
              };

              updateHeureFin = function(form) {
                if ($V(form._heure_deb) && $V(form.duree)) {
                  var time = Date.fromDATETIME("2001-01-01 " + $V(form._heure_deb));
                  time.addMinutes($V(form.duree));
                  $V(form._heure_fin   , time.toTIME(), false);
                  $V(form._heure_fin_da, time.toLocaleTime(), false);
                  Planification.checkPlanificationPatient(form);
                }
              }
            </script>
            <input name="_default_duree" type="hidden" value="{{$evenement->duree}}"/>
            {{mb_field object=$evenement form=editEvenementSSR field=_heure_deb onchange="updateDuree(this.form)"}}
            {{mb_field object=$evenement form=editEvenementSSR field=duree increment=1 size=2 step=10 onchange="updateHeureFin(this.form)"}}
            {{mb_field object=$evenement form=editEvenementSSR field=_heure_fin onchange="updateDuree(this.form)"}}
          </td>
        </tr>
      </tbody>
      <tr>
        <td colspan="2" class="button">
          <div id="warning_conflit_planification"></div>
          <button type="button" class="add" id="seance_collective_add_patient" style="display:none;" onclick="Seance.selectPatient(this.form)">
            {{tr}}CEvenementSSR-seance_collective_id-add_patients{{/tr}}
          </button>
          <button type="submit" class="submit singleclick">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div class="activite">
{{foreach from=$lines_by_element item=_lines_by_chap}}
  {{foreach from=$_lines_by_chap item=_lines_by_cat}}
    {{foreach from=$_lines_by_cat item=_lines_by_elt name=category}}
      {{foreach from=$_lines_by_elt item=_line name=elts}}
        {{mb_include module=ssr template=vw_line_alerte_ssr line=$_line include_form=0 see_alertes=1 name_form="activite"}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
{{/foreach}}
</div>

<div id="outils">
  <script>
    updateSelectedEvents = function(input_elements){
      $V(input_elements, '');
      var tab_selected = new TokenField(input_elements);
      $$(".event.selected").each(function(e){
        if(e.className.match(/CEvenementSSR-([0-9]+)/)){
         var evt_id = e.className.match(/CEvenementSSR-([0-9]+)/)[1];
         tab_selected.add(evt_id);
        }
      });
    };

    resetFormSSR = function() {
      var oForm = getForm('editSelectedEvent');
      $V(oForm.del, '0');
      $V(oForm._nb_decalage_min_debut, '');
      $V(oForm._nb_decalage_heure_debut, '');
      $V(oForm._nb_decalage_jour_debut, '');
      $V(oForm._nb_decalage_duree, '');
      $V(oForm.kine_id, '');
      $V(oForm.equipement_id, '');
    };

    onSubmitSelectedEvents = function(form) {
      updateSelectedEvents(form.event_ids);
      var values = new TokenField(form.event_ids).getValues();

      // Sélection vide
      if (!values.length) {
        alert($T('CEvenementSSR-alert-selection_empty'));
        return;
      }

      // Suppression multiple
      if ($V(form.del) == '1' && values.length > 1) {
        if (!confirm($T('CEvenementSSR-msg-confirm-multidelete', values.length) + $T('confirm-ask-continue'))) {
          return;
        }
      }

      // Envoi du formulaire
      return onSubmitFormAjax(form, function() {
        refreshPlanningsSSR();
        resetFormSSR();

        refreshAllCounts();
      } );
    };
  </script>

  <form name="editSelectedEvent" method="post" onsubmit="return onSubmitSelectedEvents(this);">
    <input type="hidden" name="m" value="ssr" />
    <input type="hidden" name="dosql" value="do_modify_evenements_aed" />
    <input type="hidden" name="event_ids" value="" />
    <input type="hidden" name="plage_groupe_ids" value="" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="sejour_id" value="{{$bilan->sejour_id}}">
    <table class="form">
     <tr>
        <th class="category" colspan="2">
          {{tr}}ssr-title_modif_evts{{/tr}}
        </th>
      </tr>
      <tr>
        <td>
          {{tr}}ssr-Move_for{{/tr}} {{mb_field object=$evenement field="_nb_decalage_min_debut" form="editSelectedEvent" increment=1 size=2 step=10}} {{tr}}common-minute|pl{{/tr}}
        </td>
        <td>
          {{tr}}ssr-Modify_duree{{/tr}} {{mb_field object=$evenement field="_nb_decalage_duree" form="editSelectedEvent" increment=1 size=2 step=10}} {{tr}}common-minute|pl{{/tr}}
        </td>
      </tr>
      <tr>
        <td>
          {{tr}}ssr-Move_for{{/tr}} {{mb_field object=$evenement field="_nb_decalage_heure_debut" form="editSelectedEvent" increment=1 size=2}} {{tr}}common-hour|pl{{/tr}}
        </td>
        <td>
          {{tr}}Transfer_to{{/tr}}
          <select name="kine_id" style="width: 12em;">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
           {{mb_include module=mediusers template=inc_options_mediuser list=$reeducateurs}}
          </select>
        </td>
      </tr>
      <tr>
        <td>
          {{tr}}ssr-Move_for{{/tr}} {{mb_field object=$evenement field="_nb_decalage_jour_debut" form="editSelectedEvent" increment=1 size=2}} {{tr}}days{{/tr}}
        </td>
        <td>
          {{tr}}Transfer_to{{/tr}}
          <select name="equipement_id" style="width: 12em;">
            <option value="">&mdash; {{tr}}CEquipement{{/tr}}</option>
            <option value="none">{{tr}}CEquipement.none{{/tr}}</option>
            {{foreach from=$plateaux item=_plateau}}
              <optgroup label="{{$_plateau->_view}}">
              {{foreach from=$_plateau->_ref_equipements item=_equipement}}
                <option value="{{$_equipement->_id}}">{{$_equipement}}</option>
              {{/foreach}}
              </optgroup>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="button" onclick="$V(this.form.del, '0'); this.form.onsubmit();" class="submit">
            {{tr}}Modify{{/tr}}
          </button>
          <button type="button" name="delete" class="trash" onclick="$V(this.form.del, '1'); this.form.onsubmit();">
            {{tr}}Delete{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>

  <form name="duplicateSelectedEvent" method="post" onsubmit="return onSubmitSelectedEvents(this);">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="dosql" value="do_duplicate_evenements_aed" />
    <input type="hidden" name="event_ids" value="" />
    <input type="hidden" name="propagate" value="" />
    <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
    <table class="form">
      <tr>
        <th colspan="2" class="category">
          {{tr}}ssr-title_duplicate_evts{{/tr}}
          <button type="button" class="info notext me-tertiary" style="float:right;" title="{{tr}}ssr-info_duplicate_evts{{/tr}}"></button>
        </th>
      </tr>
      <tr>
        <th>
          <select name="period">
            <option value="+1 WEEK">{{tr}}Week-after{{/tr}}</option>
            <option value="+1 DAY" >{{tr}}Day-after{{/tr}} </option>
            <option value="-1 DAY" >{{tr}}Day-before{{/tr}}</option>
            <option value="end_sejour" >{{tr}}ssr-duplicate-end_sejour{{/tr}}</option>
          </select>
        </th>
        <td class="button me-padding-top-4 me-text-align-left">
          <button type="button" class="duplicate singleclick" onclick="$V(this.form.propagate, '0'); this.form.onsubmit();">
            {{tr}}Duplicate{{/tr}}
          </button>
        </td>
      </tr>
      <tr>
        <th class="me-padding-top-20">
          <table style="float: right;">
            <tr>
              {{foreach from=$week_days key=_number item=_day}}
                <td>
                  <label>
                    {{$_day}}<br />
                    <input class="days nocheck" type="checkbox" name="_days[{{$_number}}]" value="{{$_number}}" />
                  </label>
                </td>
              {{/foreach}}
            </tr>
          </table>
        </th>
        <td class="button me-padding-top-20 me-text-align-left me-valign-bottom">
          <button type="button" class="new singleclick" onclick="$V(this.form.propagate, '1'); this.form.onsubmit();">
            {{tr}}Propagate{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>

  <!-- TODO: utiliser le meme formulaire pour stocker le token d'evenements pour les differentes actions  -->
  <form name="form_list_ssr" method="post">
    <input type="hidden" name="token_evts" />
  </form>

  <table class="form">
    <tr>
      <th class="category">{{tr}}Add{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        <button class="list" onclick="Seance.showEvtsCollectifsDispo('{{$sejour->_id}}');">
          {{tr}}mod-ssr-tab-vw_seances_collectives_dispo{{/tr}}
        </button>
      </td>
    </tr>
  </table>
  {{if $m !="psy"}}
  <table class="form">
    <tr>
      <th class="category">{{tr}}CEvenementSSR-code|pl{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="submit" onclick="updateSelectedEvents(getForm('form_list_ssr').token_evts); updateModalSsr();">{{tr}}CEvenement{{$m|strtoupper}}.editCodesEvenement{{/tr}}</button>
      </td>
    </tr>
  </table>
  {{/if}}

  <script>
    Main.add(function () {
      Calendar.regField(getForm("DateSelectPrintPlanningSSR").date);
    });
  </script>
  <table class="form">
    <tr>
      <th class="category">{{tr}}common-Printing|pl{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        <form name="DateSelectPrintPlanningSSR" action="?" method="get">
          <input type="hidden" name="date" class="date" value="{{$dnow}}"/>
          {{assign var=use_pdf value="ssr print_week new_format_pdf"|gconf}}
          <button type="button" class="print"
                  onclick="Planification.printPlanningSejour('{{$sejour->_id}}', $V(this.form.date), '{{$use_pdf}}');">
            {{tr}}ssr-print_current_day{{/tr}}
          </button>
        </form>
      </td>
    </tr>
  </table>
</div>
