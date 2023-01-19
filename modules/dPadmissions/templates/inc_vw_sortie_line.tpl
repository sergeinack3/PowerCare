{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=print_global value=0}}
{{assign var=number_op value=$_sejour->_ref_operations|@count}}

<td {{if $print_global}}style="display: none;"{{/if}}>
  <input type="checkbox" name="print_doc" value="{{$_sejour->_id}}" onchange="Admissions.updatePrintSelectionButtonDisplay();"/>
</td>

<td class="text CPatient-view" colspan="{{if $print_global}}1{{else}}2{{/if}}">
  {{if $canPlanningOp->read}}
    <div style="float: right; {{if $print_global}}display: none;{{/if}}" >
      {{mb_include module=system template=inc_object_notes object=$_sejour}}
    </div>
  {{/if}}

  {{if "maternite"|module_active && $_sejour->_ref_first_affectation->_ref_parent_affectation && $_sejour->_ref_first_affectation->_ref_parent_affectation->_id}}
    {{assign var=sejour_maman value=$_sejour->_ref_first_affectation->_ref_parent_affectation->_ref_sejour}}
    <img src="style/mediboard_ext/images/icons/grossesse.png" style="background-color: rgb(255, 215, 247); border-radius: 50%"
         onmouseover="ObjectTooltip.createEx(this, 'CPatient-{{$sejour_maman->patient_id}}');" />
  {{/if}}

  <span class="me-patient-view" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_patient->_guid}}');">
    {{$_sejour->_ref_patient}}
  </span>
  <div class="me-patient-details">
    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
  </div>

  <span {{if $print_global}}style="display: none;"{{/if}}>
    {{mb_include module=patients template=inc_status_icon patient=$_sejour->_ref_patient}}
  </span>
  {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_sejour->_ref_patient}}
</td>

{{* Icones contextuelles (appels contextuels, notifications, prestations,... *}}
{{if $canPlanningOp->read && $flag_contextual_icons}}
    <td {{if $print_global}}style="display: none;"{{/if}} class="narrow" style="white-space: normal">
      {{mb_include module=hospi template=inc_button_send_prestations_sejour}}

      {{if "web100T"|module_active}}
        {{mb_include module=web100T template=inc_button_iframe}}
      {{/if}}

      {{if "softway"|module_active}}
        {{mb_include module=softway template=inc_button_synthese}}
      {{/if}}

      {{if "novxtelHospitality"|module_active}}
        {{mb_include module=novxtelHospitality template=inc_button_novxtel_hospitality}}
      {{/if}}

      {{if 'notifications'|module_active}}
        {{if 'Ox\Mediboard\Notifications\CNotification::isDayAfterNotificationSent'|static_call:$_sejour}}
          <span class="circled" style="color: #007f00; border-color: #007f00; background-color: white;" title="{{tr}}CNotification-msg-sent-CSejour-day_after{{/tr}}">SMS</span>
        {{/if}}
      {{/if}}
    </td>
{{/if}}

<td class="text">
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
    {{if !$_sejour->sortie_reelle}}
      {{mb_title object=$_sejour field=entree}}
    {{/if}}
    <strong>
      {{mb_value object=$_sejour field=entree date=$date}}
      {{if $_sejour->sortie_reelle}}
        &gt; {{mb_value object=$_sejour field=sortie date=$date}}
      {{/if}}
    </strong>
  </span>
  {{if $_sejour->mode_sortie}}
    <br />{{mb_title object=$_sejour field=sortie}} :
    {{mb_value object=$_sejour field=mode_sortie}}
  {{/if}}
  {{if $_sejour->mode_sortie == "transfert" && $_sejour->etablissement_sortie_id}}
    <br />&gt; <strong>{{$_sejour->_ref_etablissement_transfert->nom|spancate:26}}</strong>
  {{/if}}
  {{if $_sejour->transport_sortie}}
    <br />{{mb_title object=$_sejour field=transport_sortie}} :
    {{mb_value object=$_sejour field=transport_sortie}}
  {{/if}}
  {{if $canAdmissions->edit}}
    {{if $_sejour->sortie_reelle}}
      <button class="edit notext me-block" type="button" onclick="Admissions.validerSortie('{{$_sejour->_id}}', false, reloadSortieLine.curry('{{$_sejour->_id}}'));">
        {{tr}}Modify{{/tr}} {{mb_label object=$_sejour field=sortie}}
      </button>
    {{else}}
      <div style="white-space: nowrap;">
        <button class="tick me-primary" type="button" onclick="Admissions.validerSortie('{{$_sejour->_id}}', false, reloadSortieLine.curry('{{$_sejour->_id}}'));">
          Valider la sortie
        </button>
      </div>
    {{/if}}
  {{/if}}
</td>

{{if "dPplanningOp CSejour use_phone"|gconf}}
  <td class="button">
    {{mb_include module=planningOp template=vw_appel_sejour type=sortie sejour=$_sejour}}
  </td>
{{/if}}

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  <td {{if $print_global}}style="display: none;"{{/if}} class="me-ws-wrap me-text-align-center">
    {{mb_include module=appFineClient template=inc_buttons_create_add_appfine refresh=0 _object=$_sejour loadJS=0}}
  </td>
{{/if}}


<td class="text">
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien classe="me-wrapped"}}
</td>

{{* DHE *}}
{{if $canPlanningOp->read}}
<td {{if $print_global}}style="display: none;"{{/if}}>
  <a class="action me-planif-icon me-button actionPat notext" title="Modifier le séjour" href="#editDHE"
     onclick="Sejour.editModal({{$_sejour->_id}}, 0, 0, reloadSorties); return false;">
    <img src="images/icons/planning.png" />
  </a>

  {{if $number_op == 1}}
    {{foreach from=$_sejour->_ref_operations item=curr_op}}
      <button class="print notext" title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
              onclick="Admissions.printDHE('operation_id', {{$curr_op->_id}}); return false;">
      </button>
    {{/foreach}}
  {{elseif $number_op > 1}}
    <button class="print notext" title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
            onclick="Admissions.chooseDHE('{{$_sejour->_id}}');" class="button print">
      {{tr}}Print{{/tr}}</button>
  {{else}}
    <button class="print notext" title="{{tr}}admissions-action-Print the DHE of the stay{{/tr}}"
            onclick="Admissions.printDHE('sejour_id', {{$_sejour->_id}}); return false;">
    </button>
  {{/if}}
</td>
{{/if}}

<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
    {{if $_sejour->presence_confidentielle}}
      {{mb_include module=planningOp template=inc_badge_sejour_conf}} <br />
    {{/if}}
    {{if ($_sejour->sortie_prevue < $date_min) || ($_sejour->sortie_prevue > $date_max)}}
      {{$_sejour->sortie_prevue|date_format:$conf.datetime}}
    {{else}}
      {{$_sejour->sortie_prevue|date_format:$conf.time}}
    {{/if}}
  </span>
  {{if $_sejour->confirme}}
    {{me_img_title src="tick.png" icon="tick" class="me-success"}}
      Sortie confirmée par le praticien
    {{/me_img_title}}
  {{/if}}
</td>
<td class="text">
  {{if !($_sejour->type == 'consult') && $_sejour->annule != 1}}
    {{if "dPadmissions sortie show_prestations_sorties"|gconf && !$print_global}}
      {{mb_include template=inc_form_prestations sejour=$_sejour edit=$canAdmissions->edit with_print=1 realise=1}}
    {{/if}}

    {{foreach from=$_sejour->_ref_affectations item=_aff}}
      <div {{if $_aff->effectue}} class="effectue" {{/if}}>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_aff->_guid}}');">
          {{$_aff->_ref_lit}}
        </span>
        {{if $_aff->lit_id}}
          ({{mb_value object=$_aff field=entree}})
        {{/if}}
      </div>
      {{foreachelse}}
      <div class="empty">Non placé</div>
    {{/foreach}}
  {{/if}}
</td>

<td class="me-ws-wrap" {{if $print_global}}style="display: none;"{{/if}}>
  {{mb_include module=forms template=inc_widget_ex_class_register object=$_sejour event_name=sortie_preparee cssStyle="display: inline-block;"}}
  {{if $canPlanningOp->read}}
    <a href="#showDocs" title="{{tr}}admissions-action-Display document and file|pl{{/tr}}" class="button"
       onclick="Admissions.showDocs('{{$_sejour->_id}}')">
      <i class="far fa-file" aria-hidden="true"></i>
      {{tr}}CCompteRendu|pl{{/tr}}
    </a>
  {{/if}}
</td>

<td {{if $print_global}}style="display: none;"{{/if}} class="me-ws-wrap">
  {{if $_sejour->sortie_preparee}}
    <button type="button" class="cancel" onclick="sortie_preparee('{{$_sejour->_id}}', '0', 0);">{{tr}}Cancel{{/tr}}</button>
  {{else}}
    <button type="button" class="tick" onclick="sortie_preparee('{{$_sejour->_id}}', '1', 1);">{{tr}}CSejour-sortie_preparee{{/tr}}</button>
  {{/if}}
</td>
{{if $app->user_prefs.show_dh_admissions}}
  {{mb_include module=admissions template=inc_operations_depassement operations=$_sejour->_ref_operations sejour=$_sejour}}
{{/if}}
