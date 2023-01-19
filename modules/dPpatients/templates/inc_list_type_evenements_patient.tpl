{{*
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=patients script=type_evenement_patient ajax=$ajax}}
{{mb_default var=type_evenement_id value=null}}

<select name="type_evenement_patient_id" style="width: 15em;" onchange="EvtPatient.updateEditEventFields(this);">
  <option value="">&mdash; {{tr}}CEvenementPatient-select-event-type{{/tr}}</option>
  {{foreach from=$types item=type}}
    <option value="{{$type->_id}}"
            data-mailing-model-id="{{$type->mailing_model_id}}"
            {{if $type_evenement_id == $type->_id}}selected{{/if}}>
      {{$type}}
    </option>
  {{/foreach}}
</select>
<button type="button" class="notext edit me-tertiary"
        onclick="TypeEvenementPatient.manage(function(){EvtPatient.refreshContentTypeEvenements('{{$type_evenement_id}}')});">
  {{tr}}Edit{{/tr}}
</button>