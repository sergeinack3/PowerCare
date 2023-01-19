{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=delegated_conf value=0}}
{{if $_message_supported->_id !== null}}
    {{assign var=delegated_conf value="fhir delegated_objects delegated_$delegated_type"|conf:$_message_supported}}
{{/if}}

{{assign var=delegated_objects value='Ox\Interop\Fhir\CExchangeFHIR::getDelegatedObject'|static_call:$_message_supported:"delegated_$delegated_type"}}

{{if $delegated_objects}}
  {{me_form_field nb_cells="0" label="$delegated_type" field_class="me-margin-left-6"}}
  <select onchange="updateDelegated(this, 'delegated_{{$delegated_type}}')" form="{{$delegated_form}}" data-type="delegated_{{$delegated_type}}">
    <option value="">---</option>
      {{foreach from=$delegated_objects item=delegated_object}}
        {{assign var=delegated_class value=$delegated_object|get_class}}
        {{assign var=explode_delegated value="\\"|explode:$delegated_class}}
        {{assign var=delegated value=$explode_delegated|@last}}

        <option {{if $delegated_conf == "$delegated"}}selected="selected"{{/if}} value="{{$delegated}}">{{$delegated}}</option>
      {{/foreach}}
  </select>
    {{/me_form_field}}
{{/if}}
