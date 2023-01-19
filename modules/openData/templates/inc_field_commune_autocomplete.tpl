{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $view_field == 1}}
  {{assign var=f value=$field}}
{{else}}
  {{assign var=f value=$view_field}}
{{/if}}

<ul style="text-align: left">
  {{foreach from=$matches item=match}}
    <li id="autocomplete-{{$match->_guid}}" data-id="{{$match->_id}}" data-LatLng="{{$match->point_geographique}}" data-guid="{{$match->_guid}}">
      {{if $template}}
        {{mb_include template=$template ignore_errors=true}}
      {{else}}
        {{mb_include module=system template=CMbObject_autocomplete nodebug=true}}
      {{/if}}
    </li>
    {{foreachelse}}
    <li>
    <span class="informal">
      {{if isset($ref_spec|smarty:nodefaults)}}
        <span class="view"></span>
      {{else}}
        <span class="view" style="display: none;">{{$input}}</span>
      {{/if}}
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </span>
    </li>
  {{/foreach}}
</ul>