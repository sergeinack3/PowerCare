{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=object value=$_ancestor.object}}
{{if $is_last && $object && is_object($object)}}
    {{assign var=delegated_type value=$_prop.object_type}}
    {{assign var=delegated_objects value='Ox\Interop\Fhir\CExchangeFHIR::getDelegatedObject'|static_call:$object:$delegated_type}}

  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
      <option {{if !$value}}selected="selected"{{/if}}>---</option>

      {{foreach from=$delegated_objects item=delegated_object}}
        <option {{if $value === $delegated_object}}selected="selected"{{/if}} value="{{$delegated_object}}">{{$delegated_object|getShortName}}</option>
      {{/foreach}}
  </select>
{{else}}
    {{if $value}}
        {{$value}}
    {{/if}}
{{/if}}
