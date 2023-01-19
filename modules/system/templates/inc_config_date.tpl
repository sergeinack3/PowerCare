{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value='editConfig'}}

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
    {{assign var=uid value=""|uniqid}}
    {{assign var=uid value="uid-$uid"}}

    <input type="text" class="{{$field}}_da {{if @$cssClass}}{{$cssClass}}{{else}}str{{/if}} {{$uid}}" name="{{$field}}_da"
           readonly value="{{$value|date_format:$conf.date|smarty:nodefaults}}" size="10"/>
    <input name="{{$field}}" class="date {{$field}}" type="hidden" value="{{$value|smarty:nodefaults}}"
           onchange="$V(this.form.getElementsByClassName('{{$field}}_da')[0], this.value.replace(/(\d{4})-(\d{2})-(\d{2})/, '$3/$2/$1'));"/>

    <script>
      Main.add(function() {
        var form = getForm('{{$form}}');
        Calendar.regField(form.getElementsByClassName('{{$field}}')[0], null, {datePicker:true, timePicker:false});
      });
    </script>

  </td>
</tr>