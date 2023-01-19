{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_vip}}
  <div class="big-warning me-align-auto">
    Vous n'avez pas accès à l'identité de ce patient.
    Veuillez contacter un administrateur
    pour avoir plus d'information sur ce problème.
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=compteRendu script=document          ajax=true}}
{{mb_script module=patients    script=patient           ajax=true}}
{{mb_script module=cabinet     script=edit_consultation ajax=true}}

<script>
  Document.refreshList = function () {
    var form = getForm("actionPat");
    if (form) {
      new Url("patients", "httpreq_vw_patient").addParam("patient_id", $V(form.patient_id)).requestUpdate("vwPatient");
    }
  };
</script>

{{if $patient->_ref_patient_links|@count}}
  <div class="small-info me-align-auto me-margin-bottom-8">
    Patient associé avec le(s) patient(s) suivant(s) :
    <ul>
      {{foreach from=$patient->_ref_patient_links item=_patient_link}}
        {{assign var=doubloon value=$_patient_link->_ref_patient_doubloon}}
        <li>
          <form name="unlink_patient_{{$doubloon->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, function() {
                  if (window.reloadPatient) {
                  reloadPatient('{{$patient->_id}}');
                  }
                  })">
            {{mb_key object=$_patient_link}}
            {{mb_class object=$_patient_link}}
            <input type="hidden" name="del" value="1">
            <button type="submit" class="unlink notext" title="{{tr}}Unlink{{/tr}}">
              {{tr}}Unlink{{/tr}}
            </button>
          </form>

          <span onmouseover="ObjectTooltip.createEx(this, '{{$doubloon->_guid}}')">
            <a href="?m=patients&tab=vw_edit_patients&patient_id={{$doubloon->_id}}">{{$doubloon->_IPP}} - {{$doubloon->_view}}</a>
          </span>
        </li>
      {{/foreach}}
    </ul>
  </div>
{{/if}}

{{mb_include module=patients template=inc_vw_identite_patient}}

<table class="form me-align-auto me-margin-bottom-8">
  <tr>
    <th class="category">{{tr}}common-action-Plan{{/tr}}</th>
  </tr>
  <tr>
    <td class="button">
      {{if "ecap"|module_active && $current_group|idex:"ecap"|is_numeric}}
        {{mb_include module=ecap template=inc_button_dhe patient_id=$patient->_id praticien_id=""}}
      {{/if}}
      {{if (!"ecap"|module_active || !$current_group|idex:"ecap"|is_numeric) || ("ecap"|module_active && $current_group|idex:"ecap"|is_numeric && 'ecap Display show_buttons_dhe'|gconf)}}
        {{if !$app->user_prefs.simpleCabinet}}
          {{assign var=buttons_list value=""}}
          {{if $canPlanningOp->edit}}
            {{me_button label=COperation icon=new
                        link="?m=planningOp&tab=vw_edit_planning&pat_id=`$patient->_id`&operation_id=0&sejour_id=0"}}
          {{/if}}
          {{if $canPlanningOp->read}}
            {{me_button label="CIntervHorsPlage-action-Interventions out of range-court" icon=new
                        link="?m=planningOp&tab=vw_edit_urgence&pat_id=`$patient->_id`&operation_id=0&sejour_id=0"}}
          {{/if}}
          {{me_dropdown_button button_icon="new" button_label=COperation
                        container_class="me-dropdown-button-right"}}

          {{if $canPlanningOp->read}}
            <a class="button new" href="?m=planningOp&tab=vw_edit_sejour&patient_id={{$patient->_id}}&sejour_id=0">
              {{tr}}CSejour{{/tr}}
            </a>
          {{/if}}
        {{/if}}
      {{/if}}
      {{if $canCabinet->read}}
        {{me_button label=CConsultation link="?m=cabinet&tab=edit_planning&pat_id=`$patient->_id`&consultation_id=" icon=new}}
        {{me_button label="CConsultation-action-Immediate"  icon=new
                    onclick="Consultation.openConsultImmediate('`$patient->_id`', '', '', '', '', 'tous')"}}
        {{me_dropdown_button button_icon="new" button_label=CConsultation container_class="me-dropdown-button-right"}}
      {{/if}}
    </td>
  </tr>
</table>

{{if "doctolib"|module_active && "doctolib staple_authentification client_access_key_id"|gconf}}
  {{mb_include module=doctolib template=buttons/inc_vw_buttons_patient patient_id=$patient->_id}}
{{/if}}

<table class="form me-align-auto me-margin-bottom-8">
  {{assign var="affectation" value=$patient->_ref_curr_affectation}}
  {{if $affectation && $affectation->affectation_id}}
    <tr>
      <th colspan="3" class="category">Chambre actuelle</th>
    </tr>
    <tr>
      <td colspan="3">
        {{$affectation->_ref_lit}}
        depuis le {{mb_value object=$affectation field=entree}}
      </td>
    </tr>
    {{assign var="affectation" value=$patient->_ref_next_affectation}}
  {{elseif $affectation && $affectation->affectation_id}}
    <tr>
      <th colspan="3" class="category">Prochaine chambre</th>
    </tr>
    <tr>
      <td colspan="3">
        {{$affectation->_ref_lit}}
        depuis le {{mb_value object=$affectation field=entree}}
      </td>
    </tr>
  {{/if}}

  {{mb_include module=patients template=inc_vw_patient_events}}

  {{if "maternite"|module_active && $patient->_ref_grossesses}}
    {{foreach from=$patient->_ref_grossesses item=grossesse}}
      <tr>
        <th colspan="2" class="category">Grossesse (terme prévu : {{$grossesse->terme_prevu|date_format:$conf.date}})</th>
      </tr>
      <tr>
        <td colspan="2">Séjours</td>
      </tr>
      {{foreach from=$grossesse->_ref_sejours item=object}}
        {{mb_include module=patients template=CSejour_event}}
        {{foreachelse}}
        <td colspan="2" class="empty">{{tr}}CSejour.none{{/tr}}</td>
      {{/foreach}}
      <tr>
        <td colspan="2">Consultations</td>
      </tr>
      {{foreach from=$grossesse->_ref_consultations item=object}}
        {{mb_include module=patients template=CConsultation_event show_semaine_grossesse=1}}
        {{foreachelse}}
        <td colspan="2" class="empty">{{tr}}CConsultation.none{{/tr}}</td>
      {{/foreach}}
    {{/foreach}}
  {{/if}}
</table>
