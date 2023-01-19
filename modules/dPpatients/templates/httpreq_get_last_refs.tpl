{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="float:right; text-align: center">
  <fieldset class="me-text-align-left">
    <legend>{{tr}}Rapid_acces{{/tr}}</legend>
    {{mb_include module=patients template="inc_patient_planification" ajax=1 patient_id=$patient->_id consult=$consultation mode_cabinet=$mode_cabinet}}
    <br />
    {{mb_include module=cabinet template="inc_patient_infos" vertical=1 ajax=1}}
  </fieldset>
  {{if 'notifications'|module_active}}
    <fieldset>
      <legend>{{tr}}common-Notification|pl{{/tr}}</legend>
      <form name="editPatientSMS" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$patient}}
        {{mb_key object=$patient}}

        <table class="form me-no-box-shadow" style="background-color: inherit!important;">
          <tr>
            {{me_form_bool nb_cells=2 mb_object=$patient mb_field="allow_sms_notification"}}
              {{mb_field object=$patient field=allow_sms_notification onchange="this.form.onsubmit();"}}
            {{/me_form_bool}}
          </tr>
          <tr>
            {{me_form_field nb_cells=2 mb_object=$patient mb_field="phone_area_code"}}
              {{mb_field object=$patient field=phone_area_code onchange="this.form.onsubmit();"}}
            {{/me_form_field}}
          </tr>
          <tr>
            {{me_form_field nb_cells=2 mb_object=$patient mb_field=tel2}}
              {{mb_field object=$patient field=tel2 onchange="this.form.onsubmit();"}}
            {{/me_form_field}}
          </tr>
          <tr>
            {{me_form_field nb_cells=2 mb_object=$patient mb_field=email}}
              {{mb_field object=$patient field=email onchange="this.form.onsubmit();"}}
            {{/me_form_field}}
          </tr>
        </table>
      </form>
    </fieldset>
  {{/if}}
</div>

<table class="me-patient-infos">
  <tr>
    <td style="font-weight: bold;" class="me-patient-name">
      <a href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
           {{$patient}} &mdash; {{$patient->_age}}
        </span>
      </a>
    </td>
  </tr>
  {{if $past_consults|@count}}
    <tr>
      <td>
        {{mb_include module=cabinet template=info_consults_no_regle}}
      </td>
    </tr>
  {{/if}}
  {{if $patient->_ref_dossier_medical->_id && $patient->_ref_dossier_medical->_ref_evenements_patient|@count}}
    <tr>
      <td class="text">
        <div class="small-warning">
          <strong>{{tr}}CEvenementPatient._list_rappel{{/tr}}: </strong>
          <ul>
            {{foreach from=$patient->_ref_dossier_medical->_ref_evenements_patient item=_evt}}
              <li>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_evt->_guid}}')">
                  {{mb_value object=$_evt field=date}}: {{$_evt}}
                  {{if $_evt->praticien_id}}par {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_evt->_ref_praticien}}{{/if}}
                </span>
              </li>
            {{/foreach}}
          </ul>
        </div>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td class="text me-patient-infos-list">
      <strong>{{tr}}CSejour|pl{{/tr}}: </strong>
      <ul>
        {{foreach from=$patient->_ref_sejours item=_sejour}}
          <li>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
            {{$_sejour}} 
          </span>
            <ul>
              {{foreach from=$_sejour->_ref_operations item=_op}}
                <li style="list-style-type: none;" class="iconed-text interv">
                  {{if $is_anesth}}
                    {{assign var=operations_ids value='Ox\Core\CMbArray::pluck'|static_call:$consultation->_refs_dossiers_anesth:"operation_id"}}
                    <input type="radio" name="_operation_id" value="{{$_op->operation_id}}"
                           {{if in_array($_op->operation_id, $operations_ids)}}checked{{/if}} />
                  {{/if}}
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}')">
                {{tr}}dPplanningOp-COperation of{{/tr}} {{mb_value object=$_op field=_datetime}}
              </span>
                  {{tr}}With_chir{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}
                  {{if $_op->annulee}}<span style="color: red;">[ANNULE]</span>{{/if}}
                </li>
                {{foreachelse}}
                <li class="empty">{{tr}}COperation.none{{/tr}}</li>
              {{/foreach}}

              {{foreach from=$_sejour->_ref_consultations item=_consult}}
                <li style="list-style-type: none;" class="{{if $_consult->annule}}cancelled{{/if}} iconed-text {{$_consult->_type}}">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
              {{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult field=_datetime}}
              </span>
                  {{tr}}With_chir{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_praticien}}
                  {{if $_consult->annule}}<span style="color: red;">[{{tr}}CConsultation-annule-upper{{/tr}}]</span>{{/if}}
                </li>
              {{/foreach}}
            </ul>
          </li>
          {{foreachelse}}
          <li class="empty">{{tr}}CSejour.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
  
  <tr>
    <td class="text me-patient-infos-list">
      <strong>{{tr}}CConsultation|pl{{/tr}}:</strong>
      <ul>
        {{foreach from=$patient->_ref_consultations item=_consult}}
          <li class="{{if $_consult->annule}}cancelled{{/if}} iconed-text {{$_consult->_type}}">
            {{assign var=facture value=$_consult->_ref_facture}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
              {{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult field=_datetime}}
            </span>
            {{if count($facture->_ref_notes)}}
              {{mb_include module=system template=inc_object_notes object=$facture float=left}}
            {{/if}}
            {{tr}}With_chir{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_praticien}}
            {{if $_consult->annule}}<span style="color: red;">[ANNULE]</span>{{/if}}
          </li>
          {{foreachelse}}
          <li class="empty">{{tr}}CConsultation.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>
