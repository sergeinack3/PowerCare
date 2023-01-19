{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=textarea value=0}}
{{mb_default var=rows value=2}}
{{mb_default var=spinner_min  value=0}}

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
  <th {{if @$thcolspan}}colspan="{{$thcolspan}}"{{else}}style="width: 50%"{{/if}}>
    <label for="{{$field}}" title="{{tr}}{{$locale}}-desc{{/tr}}">
      {{tr}}{{$locale}}{{/tr}}
    </label>  
  </th>
  
  <td {{if @$tdcolspan}}colspan="{{$tdcolspan}}"{{/if}}>
    {{unique_id var=uid}}
    {{assign var=uid value="uid-$uid"}}
    
    {{if @$numeric}}
      <script type="text/javascript">
        Main.add(function(){
          $$(".{{$uid}}")[0].addSpinner({min: {{$spinner_min}} {{if @$spinner_max}}, max: {{$spinner_max}}{{/if}}});
        });
      </script>
    {{/if}}
    
    {{if $textarea}}
      <textarea rows="{{$rows}}" class="{{if @$cssClass}}{{$cssClass}}{{else}}str{{/if}} {{$uid}}" name="{{$field}}">{{$value|smarty:nodefaults}}</textarea>
    {{else}}
      {{if @$prefix}}{{$prefix}}{{/if}}

      <input class="{{if @$cssClass}}{{$cssClass}}{{else}}str{{/if}} {{$uid}}" {{if @$password}} type="password" {{/if}} name="{{$field}}" 
             value="{{$value|smarty:nodefaults}}" {{if @$size}}size="{{$size}}"{{/if}}
             {{if @$maxlength}}maxlength="{{$maxlength}}"{{/if}}/>
    {{/if}}
  </td>
</tr>
