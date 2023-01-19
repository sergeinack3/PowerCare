{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var=consultation   value=$object}}
{{assign var=patient        value=$consultation->_ref_patient}}
{{assign var=consult_anesth value=$consultation->loadRefConsultAnesth()}}

{{assign var=can_edit value=0}}

{{if !"dPcabinet CConsultation verification_access"|gconf
  || ($object->sejour_id || ($consult_anesth->_id && $consult_anesth->sejour_id) || $object->_can->edit)}}
  {{assign var=can_edit value=1}}
{{/if}}

{{if !$patient->_id}}
    {{assign var=patient value=$consultation->loadRefPatient()}}
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="2">
      {{if $can_edit}}
        {{mb_include module=system template=inc_object_notes     }}
        {{mb_include module=system template=inc_object_idsante400}}
        {{mb_include module=system template=inc_object_history   }}
      {{/if}}
      {{$consultation}}
    </th>
  </tr>
  {{if $patient->_id}}
  <tr>
    <td rowspan="3" style="width: 1px;">
      {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
    </td>
    <td>
      {{assign var=see_button_patient value=0}}
      {{if in_array($app->user_prefs.UISTYLE, array("tamm", "pluus")) && $patient->canEdit()}}
        {{assign var=see_button_patient value=1}}
        <button type="button" class="edit notext me-tertiary me-dark" style="float:left;" onclick="Patient.editModal('{{$patient->_id}}')">
          {{tr}}Modify{{/tr}}
        </button>
      {{/if}}
      <strong {{if $see_button_patient}}style="line-height: 20px"{{/if}}>{{mb_value object=$patient}}</strong>
    </td>
  </tr>
  {{elseif $consultation->groupee && $consultation->no_patient}}
    <tr>
      <td style="background-color: #e5b774;">
        [{{tr}}CConsultation-MEETING{{/tr}}] ({{tr}}CConsultation-no_patient{{/tr}})
      </td>
    </tr>
  {{elseif !$consultation->patient_id}}
    <tr>
      <td style="background-color: #ffa;">
        [{{tr}}CConsultation-PAUSE{{/tr}}]
      </td>
    </tr>
  {{/if}}
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consultation->_ref_praticien}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_value object=$consultation field=_datetime}}
    </td>
  </tr>
  {{if $consultation->_ref_categorie->_id}}
    <tr>
      <td colspan="2">
        {{tr}}CConsultation-categorie_id{{/tr}} :
        {{mb_include module=cabinet template=inc_icone_categorie_consult consultation=$consultation categorie=$consultation->_ref_categorie display_name=true}}
      </td>
    </tr>
  {{/if}}
  {{if $can_edit}}
    {{if $consultation->motif && !$app->user_prefs.limit_prise_rdv}}
      <tr>
        <td colspan="2" class="text">
            {{$consultation->motif|nl2br|html_entity_decode}}
        </td>
      </tr>
    {{/if}}
    {{if $consultation->rques && !$consultation->annule}}
    <tr>
      <td colspan="2" class="text">
          {{$consultation->rques|nl2br|html_entity_decode}}
      </td>
    </tr>
    {{/if}}
  {{/if}}
  {{if $consultation->annule}}
    <tr>
      <th class="category cancelled" colspan="2">
      {{tr}}CConsultation-annule{{/tr}}
      {{if $consultation->motif_annulation}}
          ({{tr}}CConsultation.motif_annulation.{{$consultation->motif_annulation}}{{/tr}})
      {{/if}}
      </th>
    </tr>
  {{/if}}

  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    <tr>
      <td colspan="2">
        {{mb_include module=appFineClient template=count_orders_tooltip}}
      </td>
    </tr>
  {{/if}}

  {{mb_default var=dossier_anesth_id value=""}}
  {{if "oxCabinet"|module_active && in_array($app->user_prefs.UISTYLE, array("tamm", "pluus"))}}
    {{mb_include module=oxCabinet template=inc_consultation_tooltip_actions}}
  {{else}}
    {{if $object->_can->edit}}
      <tr>
        <td class="button" colspan="2">
          {{mb_script module="dPcabinet" script="edit_consultation" ajax="true"}}
          <button type="button" class="change" onclick="Consultation.editRDVModal('{{$consultation->_id}}',
            '{{$consultation->_ref_chir->_id}}',
            '{{$consultation->plageconsult_id}}',
            '{{$consultation->patient_id}}')">
            {{tr}}Rendez-vous{{/tr}}
          </button>
         {{if $patient->_id}}
            <button type="button" class="edit" onclick="Consultation.editModal('{{$consultation->_id}}', null, '{{$dossier_anesth_id}}');">
              {{tr}}CConsultation{{/tr}}
            </button>
           {{if $consultation->type_consultation == "consultation"}}
            <button type="button" class="edit" onclick="Consultation.editModal('{{$consultation->_id}}', 'facturation', '{{$dossier_anesth_id}}')">
              {{tr}}Reglement{{/tr}}
            </button>
           {{/if}}
            {{if $consultation->chrono != 64 && $consultation->_date == $dnow}}
              <form method="post" name="finish_consult_{{$consultation->_id}}" onsubmit="return onSubmitFormAjax(this, {})">
                <input type="hidden" name="chrono" value="64"/>
                {{mb_key object=$consultation}}
                {{mb_class object=$consultation}}
                <button type="button" class="tick" onclick="this.form.onsubmit();">
                  {{tr}}CConsultation-action-Finish{{/tr}}
                </button>
              </form>
            {{/if}}

            {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf && $consultation->sejour_id}}
              {{assign var=demandeBrancardage value="Ox\Mediboard\Brancardage\CBrancardage::DEMANDE_DE_BRANCARDAGE"|constant}}
              {{assign var=arrivee value="Ox\Mediboard\Brancardage\CBrancardage::ARRIVEE"|constant}}

              {{assign var=brancardage_aller
              value='Ox\Mediboard\Brancardage\Utilities\CBrancardageGetUtility::getStepBrancardage'|static_call:$consultation:'aller'}}

              <div id="brancardage-{{$consultation->_guid}}" style="float: right;" class="me-display-flex me-float-initial">
                {{**** Brancardage aller ****}}
                {{mb_include module=brancardage template=inc_exist_brancard colonne=$demandeBrancardage
                object=$consultation brancardage_to_load="aller"}}

                {{**** Brancardage retour ****}}
                {{if 'Ox\Mediboard\Brancardage\Utilities\CBrancardageCheckUtility::checkTimingAlreadyExist'|static_call:$brancardage_aller:$arrivee}}
                    {{mb_include module=brancardage template=inc_exist_brancard colonne=$demandeBrancardage
                    object=$consultation brancardage_to_load="retour"}}
                {{/if}}
              </div>
            {{/if}}

           {{if "transport"|module_active}}
             {{mb_include module=transport template=inc_buttons_transport object=$consultation}}
           {{/if}}
         {{/if}}

        {{if "cda"|module_active}}
          {{mb_script module=cda script=ccda ajax=true}}
          <button class="fas fa-plus" type="button" onclick="Ccda.generateVSM('{{$consultation->_id}}', '{{$consultation->_class}}');">{{tr}}CDA-msg-generate VSM{{/tr}}</button>
        {{/if}}
        </td>
      </tr>
    {{/if}}
  {{/if}}
</table>

{{if $can_edit && !$app->user_prefs.limit_prise_rdv}}
  {{mb_include module=cabinet template=inc_list_actes_ccam subject=$consultation vue=view extra=total}}
  {{mb_include module=cabinet template=inc_list_actes_ngap subject=$consultation }}
  {{mb_include module=cabinet template=inc_list_actes_lpp subject=$consultation }}

  {{assign var=examaudio value=$consultation->_ref_examaudio}}
  {{if $examaudio && $examaudio->_id}}
    <script type="text/javascript">
      newExam = function(sAction, consultation_id) {
        if (sAction) {
          var url = new Url("dPcabinet", sAction);
          url.addParam("consultation_id", consultation_id);
          url.popup(900, 600, "Examen");
        }
      }
    </script>
    <a href="#{{$examaudio->_guid}}" onclick="newExam('exam_audio', '{{$consultation->_id}}')">
      <strong>{{tr}}CExamAudio-long{{/tr}}</strong>
    </a>
  {{/if}}
{{/if}}
