{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode_vue value=vertical}}
{{assign var=patient value=$_consult->_ref_patient}}
{{assign var=sejour  value=$_consult->_ref_sejour}}

{{if !$patient->_id}}
  {{assign var="style" value="background-color: #ffa;"}}
  {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var="style" value="background-color: #64644a;"}}
  {{/if}}
{{elseif $_consult->premiere}}
  {{assign var="style" value="background-color: #faa;"}}
  {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var="style" value="background-color: #644a4a;"}}
  {{/if}}
{{elseif $_consult->derniere}}
  {{assign var="style" value="background-color: #faf;"}}
  {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var="style" value="background-color: #644a64;"}}
  {{/if}}
{{elseif $sejour->_id}}
  {{assign var="style" value="background-color: #cfa;"}}
  {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var="style" value="background-color: #55644a;"}}
  {{/if}}
{{else}} 
  {{assign var="style" value=""}}
{{/if}}

{{assign var=prat_id value=$_plage->chir_id}}
{{assign var=consult_id value=$_consult->_id}}

{{assign var=destinations value=""}}
{{if
  isset($listPlages|smarty:nodefaults) && $listPlages|array_key_exists:$prat_id && $listPlages.$prat_id.destinations
  && @count($listPlages.$prat_id.destinations) && $canCabinet->edit
}}
  {{assign var=destinations value=$listPlages.$prat_id.destinations}}
{{/if}}

<tbody class="hoverable">

<tr class="{{if $_consult->_id == $consult->_id}}selected{{/if}}">
  {{assign var=categorie value=$_consult->_ref_categorie}}
  <td class="text me-w60px {{if $_consult->annule}}cancelled{{/if}} {{if $_consult->chrono == $_consult|const:'TERMINE'}}hatching{{/if}}"
      style="{{if $_consult->_id != $consult->_id}}{{$style}}{{/if}}" rowspan="2">
    {{if $destinations && !@$offline && $mode_vue == "horizontal"}}
      <form name="ChangePlage-{{$_consult->_guid}}" action="?m={{$current_m}}" method="post">
        
        <input type="hidden" name="dosql" value="do_consultation_aed" />
        <input type="hidden" name="m" value="dPcabinet" />
        <input type="hidden" name="del" value="0" />
        
        {{mb_key object=$_consult}}
        
        <select name="plageconsult_id" onchange="this.form.submit();" style="width: 2em;">
          <option value="">
            &mdash; {{tr}}Transfer{{/tr}}
          </option>
          {{foreach from=$destinations item=destination}}
          <option value={{$destination->_id}}>
            {{$destination->_ref_chir->_view}}
            : {{$destination->debut|date_format:$conf.time}} 
            - {{$destination->fin|date_format:$conf.time}}
            {{if $destination->libelle}} - {{$destination->libelle}}{{/if}}
          </option>
          {{/foreach}}
        </select>
    
      </form>
      <br/>
    {{/if}}
    {{if $canCabinet->read && !@$offline}}
      <a href="#1" class="me-planif-icon me-button me-tertiary me-low-emphasis notext me-small"
         onclick="Consultation.editRDVModal('{{$_consult->_id}}', null, null, '{{$_consult->patient_id}}'); return false;" title="Modifier le RDV"
         {{if $mode_vue == "vertical"}}style="float: right;"{{/if}}>
        <img src="images/icons/planning.png" title="{{tr}}Edit{{/tr}}" />
      </a>
      {{if $mode_vue == "horizontal"}}
        <br />
      {{/if}}
    {{/if}}
    
    {{if $patient->_id}}
      {{if $canCabinet->read && !@$offline}}
        <a href="#1" onclick="Consultation.edit('{{$_consult->_id}}'); return false;" style="margin-bottom: 4px;">
      {{else}}
        <a href="#1" title="Impossible d'accéder à la consultation"> {{if $mode_vue == "horizontal"}}<br />{{/if}}
      {{/if}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
        {{$_consult->heure|truncate:5:"":true}}
      </span>
      </a>
    {{else}}
      {{$_consult->heure|truncate:5:"":true}}
    {{/if}}

    {{if $_consult->teleconsultation && 'teleconsultation'|module_active}}
        {{mb_include module=teleconsultation template=inc_shortcut_teleconsultation onclick="if(Teleconsultation.checkTeleconsultInProcess()){Consultation.edit('$consult_id', null, '1');}"}}
    {{/if}}

    {{if $patient->_id}}
      {{if ($_consult->chrono == $_consult|const:'PLANIFIE') && !@$offline}}
        <form name="etatFrm{{$_consult->_id}}" action="?m={{$current_m}}" method="post">
          <input type="hidden" name="m" value="dPcabinet" />
          <input type="hidden" name="dosql" value="do_consultation_aed" />
          {{mb_key object=$_consult}}
          <input type="hidden" name="chrono" value="{{$_consult|const:'PATIENT_ARRIVE'}}" />
          <input type="hidden" name="arrivee" value="" />
        </form>

        <a style="white-space: nowrap;" href="#1"
           onclick="var callback = putArrivee.curry(document.etatFrm{{$_consult->_id}});
             if (window.IdentityValidator) {
               IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}', callback);
             }
             else {
               callback();
             }">
          {{me_img src="check.png" title="CConsultation-notify_arrive-patient" icon="tick" class="me-primary"}}
          {{if $mode_vue == "horizontal"}}<br />{{/if}}
          {{$_consult->_etat}}
        </a>
      {{else}}
        {{$_consult->_etat}}
      {{/if}}
    {{/if}}
  </td>
  
  <td class="text" style="{{$style}}">
    {{if $patient->_id}}
      {{if @$offline}}
        <div id="{{$patient->_guid}}-dossier" style="display: none; min-width: 600px;">
          <button class="print not-printable" onclick="modalWindow.print()">{{tr}}Print{{/tr}}</button>
          <button class="cancel not-printable" onclick="modalWindow.close();" style="float: right;">{{tr}}Close{{/tr}}</button>
          
          {{assign var=patient_id value=$patient->_id}}
          {{$patients_fetch.$patient_id|smarty:nodefaults}}
        </div>
        
        <a href="#1" onclick="modalWindow = Modal.open($('{{$patient->_guid}}-dossier'))" style="display: inline-block;">
      {{elseif $canCabinet->edit}}
        <a href="#1" onclick="Consultation.edit('{{$_consult->_id}}'); return false;" style="display: inline-block;">
      {{else}}
        <a href="#1" title="Impossible d'accéder à la consultation" style="display: inline-block;">
      {{/if}}

        <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
          {{$patient->_view|truncate:30:"...":true}}
          {{if $patient->_annees != "??"}}
            ({{$patient->_age}})
          {{/if}}
        </strong>

        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </a>
      {{if $sejour->entree_reelle}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">({{$sejour->entree_reelle|date_format:$conf.time}})</span>
      {{/if}}

      {{mb_include module=cabinet template=inc_icone_dhe_associe}}
    {{elseif $_consult->groupee && $_consult->no_patient}}
      [{{tr}}CConsultation-MEETING{{/tr}}]
    {{else}}
      [{{tr}}CConsultation-PAUSE{{/tr}}]
    {{/if}}
    <div class="compact" sttle="text-overflow: ellipsis; overflow: hidden;">

      {{if $destinations && !@$offline && $mode_vue == "vertical"}}
        <form name="ChangePlage-{{$_consult->_guid}}" action="?m={{$current_m}}" method="post" style="float: right;">
          <input type="hidden" name="dosql" value="do_consultation_aed" />
          <input type="hidden" name="m" value="dPcabinet" />
          <input type="hidden" name="del" value="0" />
          {{mb_key object=$_consult}}

          <select name="plageconsult_id" onchange="this.form.submit();" style="font-size: 9px; width: 80px">
            <option value="">
              &mdash; {{tr}}Transfer{{/tr}}
            </option>
            {{foreach from=$destinations item=destination}}
              <option value={{$destination->_id}}>
                {{$destination->_ref_chir->_view}}
                : {{$destination->debut|date_format:$conf.time}}
                - {{$destination->fin|date_format:$conf.time}}
                {{if $destination->libelle}} - {{$destination->libelle}}{{/if}}
              </option>
            {{/foreach}}
          </select>
        </form>
      {{/if}}

      {{if $patient->_id}}
        <span
          {{if $canCabinet->edit && !@$offline}}
          onclick="Consultation.edit('{{$_consult->_id}}');
          {{/if}}
          onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{mb_value object=$_consult field=motif}}
        </span>
        {{if $board}}
          {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$patient display_center=0 float_right=0 load_js=0}}
        {{/if}}
      {{else}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{mb_value object=$_consult field=motif}}
        </span>
      {{/if}}
    </div>
  </td>

    <td rowspan="2" style="{{$style}}" class="narrow">
      {{if $categorie->nom_icone}}
      {{mb_include module=cabinet template=inc_icone_categorie_consult
        consultation=$_consult
        categorie=$categorie
        onclick="IconeSelector.changeCategory('$consult_id', this)"}}
      {{/if}}
    </td>
</tr>


</tbody>
