{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr class="clear">
    <th colspan="12">
      <h1>
        <a href="#" onclick="window.print()">
          {{tr var1=$date|date_format:$conf.longdate}}admissions-Pre-admission of %s|pl{{/tr}} ({{$total}} {{tr}}admissions-pre-admission|pl{{/tr}})
        </a>
      </h1>
    </th>
  </tr>
  <tr>
    <th colspan="3"><strong>{{tr}}CPatient{{/tr}}</strong></th>
    <th colspan="2"><strong>{{tr}}CConsultAnesth{{/tr}}</strong></th>
    <th colspan="7"><strong>{{tr}}module-dPhospi-court{{/tr}}</strong></th>
  </tr>
  <tr>
    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
      <th>{{tr}}AppFine{{/tr}}</th>
    {{/if}}
    <th>{{tr}}CPatient-Last name / First name{{/tr}}</th>
    <th>{{tr}}CPatient-Birth (Age){{/tr}}</th>
    <th>{{tr}}CPatient-sexe{{/tr}}</th>
    <th>{{tr}}Hour{{/tr}}</th>
    <th>{{tr}}COperation-anesth_id{{/tr}}</th>
    <th>{{tr}}common-Practitioner{{/tr}}</th>
    <th>{{tr}}CSejour-_admission-court{{/tr}}</th>
    <th>{{tr}}CSejour-_type_admission-court{{/tr}}</th>
    <th>{{tr}}CChambre{{/tr}}</th>
    <th>{{tr}}CPrestation-court{{/tr}}</th>
    <th>{{tr}}CPatient-c2s-court{{/tr}}</th>
    <th>{{tr}}CActeCCAM-montant_depassement-court{{/tr}}</th>
  </tr>
  {{foreach from=$listConsultations item=curr_consult}}
  <tr>
    {{assign var=patient value=$curr_consult->_ref_patient}}
    {{assign var=dossiers_anesth value=$curr_consult->_refs_dossiers_anesth}}
    {{if is_array($curr_consult->_next_sejour_and_operation)}}
      {{if $curr_consult->_next_sejour_and_operation.COperation->_id}}
        {{assign var=curr_adm value=$curr_consult->_next_sejour_and_operation.COperation->_ref_sejour}}
        {{assign var=type_event value="COperation"}}
      {{else}}
        {{assign var=curr_adm value=$curr_consult->_next_sejour_and_operation.CSejour}}
        {{assign var=type_event value="CSejour"}}
      {{/if}}
    {{/if}}

    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
      <td class="button">
        {{mb_include module=appFineClient template=inc_create_account_appFine idex=$curr_consult->_ref_patient->_ref_appFine_idex patient=$curr_consult->_ref_patient}}
      </td>
    {{/if}}

    <td class="text" rowspan="{{$dossiers_anesth|@count}}">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
        {{$patient->_view}}
      </span>
    </td>
    <td rowspan="{{$dossiers_anesth|@count}}">
      {{mb_value object=$patient field="naissance"}} ({{$patient->_age}})
    </td>
    <td rowspan="{{$dossiers_anesth|@count}}">
      {{$patient->sexe}}
    </td>
    <td class="text" rowspan="{{$dossiers_anesth|@count}}">
      {{if $curr_consult->_id}}
        <div class="{{if $curr_consult->chrono == 64}}small-success{{/if}}" style="margin: 0;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_consult->_guid}}')">{{$curr_consult->heure|date_format:$conf.time}}</span>
        </div>
      {{else}}
        <div class="small-warning" style="margin: 0;">
          {{tr}}admissions-msg-Pre-anesthetic consultation not created{{/tr}}
        </div>
      {{/if}}
    </td>
    <td rowspan="{{$dossiers_anesth|@count}}">
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_consult->_ref_plageconsult->_ref_chir}}
    </td>

    {{foreach from=$dossiers_anesth item=_dossier name=dossiers_anesth}}
      {{if !$smarty.foreach.dossiers_anesth.first}}
        <tr>
      {{/if}}

      {{if $_dossier->_etat_dhe_anesth != "non_associe"}}
        {{assign var=_sejour value=""}}
        {{if $_dossier->_ref_sejour->_id}}
          {{assign var=_sejour value=$_dossier->_ref_sejour}}
        {{elseif $curr_consult->_next_sejour_and_operation.CSejour->_id}}
          {{assign var=_sejour value=$curr_consult->_next_sejour_and_operation.CSejour}}
        {{/if}}

        <td class="text">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
        </td>

        <td class="text">
          <div>
            {{if "dmp"|module_active}}
              <span style="float: right;">
              {{mb_include module=dmp template=inc_button_dmp patient=$patient compact=true}}
            </span>
            {{/if}}
            {{mb_include module=system template=inc_object_notes object=$_sejour float=right}}
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
          </div>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{if $_sejour->presence_confidentielle}}
            {{mb_include module=planningOp template=inc_badge_sejour_conf}}
          {{/if}}
          {{$_sejour->entree|date_format:$conf.date}}
        </span>
        </td>

        <td class="text">
          {{mb_value object=$_sejour field=type}}
        </td>

        {{if !$_sejour->annule && $_dossier && $_dossier->_ref_sejour->_id}}

          <td class="text">
            {{mb_include module=hospi template=inc_placement_sejour sejour=$_sejour}}
          </td>

          <td class="text">{{$_sejour->_ref_prestation->_view}}</td>

          <td>
            {{if $_sejour->_couvert_c2s}}
              {{me_img_title src="tick.png" icon="tick" class="me-success"}}
                Droits C2S en cours
              {{/me_img_title}}
            {{else}}
              -
            {{/if}}
          </td>

          <td>
            {{foreach from=$_sejour->_ref_operations item=_op}}
              {{if $_op->depassement}}
                {{mb_value object=$_op field="depassement"}}
                <br />
              {{/if}}
              {{foreachelse}}
              -
            {{/foreach}}
          </td>

        {{elseif $_sejour->annule}}
          <td colspan="4" class="cancelled">
            {{tr}}Cancelled{{/tr}}
          </td>
        {{else}}
          <td colspan="4" class="button">
            {{if $type_event == "COperation"}}
              {{tr}}admissions-msg-Intervention not associated with consultation{{/tr}}
            {{else}}
              {{tr}}admissions-msg-Stay not associated with the consultation{{/tr}}
            {{/if}}
          </td>
        {{/if}}

      {{else}}
        <td colspan="7" class="button">
          <span class="texticon texticon-stup texticon-stroke" style="white-space: nowrap;"
                title="{{tr}}CConsultation-_etat_dhe_anesth-non_associe{{/tr}}">
            {{tr}}COperation-event-dhe{{/tr}}
          </span>
        </td>
      {{/if}}
    {{/foreach}}
    </tr>
  {{/foreach}}
</table>
