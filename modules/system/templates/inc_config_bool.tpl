{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=thcolspan value=""}}
{{mb_default var=tdcolspan value=""}}
{{mb_default var=onchange  value=""}}

{{if @$m}}
  {{if @$class}}
    {{assign var=field  value="$m[$class][$var]"}}
    {{assign var=value  value=$conf.$m.$class.$var}}
    {{assign var=locale value=config-$m-$class-$var}}
  {{else}}
    {{assign var=field  value="$m[$var]"}}
    {{assign var=value  value=$conf.$m.$var}}
    {{assign var=locale value=config-$m-$var}}
  {{/if}}
{{else}}
  {{assign var=field  value="$var"}}
  {{assign var=value  value=$conf.$var}}
  {{assign var=locale value=config-$var}}
{{/if}}
  
<tr>
  <th {{if $thcolspan}}colspan="{{$thcolspan}}"{{else}}style="width: 50%;"{{/if}}>
    <label for="{{$field}}" title="{{tr}}{{$locale}}-desc{{/tr}}">
      {{tr}}{{$locale}}{{/tr}}
    </label>  
  </th>

  <td {{if $tdcolspan}}colspan="{{$tdcolspan}}"{{/if}}>
    <label for="{{$field}}_1">{{tr}}bool.1{{/tr}}</label>
    <input type="radio" name="{{$field}}" value="1" {{if $value == "1"}}checked{{/if}} {{if $onchange}}onchange="{{$onchange}}"{{/if}} />
    <label for="{{$field}}_0">{{tr}}bool.0{{/tr}}</label>
    <input type="radio" name="{{$field}}" value="0" {{if $value == "0" || $value == ""}}checked{{/if}} {{if @$onchange}}onchange="{{$onchange}}"{{/if}} />
  </td>
</tr>
