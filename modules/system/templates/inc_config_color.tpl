{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

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

    <input name="{{$field}}" value="{{$value}}" type="hidden" />

    <script>
      Main.add(function(){
        var e = getForm('{{$form}}').elements['{{$field}}'];
        e.colorPicker({
          allowEmpty: true,
          change: function(color) {
            this.value = color ? color.toHexString() : '';
          }.bind(e)
        });
      });
    </script>
  </td>
</tr>
