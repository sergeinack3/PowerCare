{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Filter"
      action="?m={{$m}}&{{$actionType}}={{$action}}&dialog={{$dialog}}&id_sante400_id=0{{if $dialog}}&object_class={{$filter->object_class}}&object_id={{$filter->object_id}}{{/if}}"
      method="get" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="tab" value="{{$tab}}"/>
  <input type="hidden" name="dialog" value="{{$dialog}}"/>
  <table class="form">
    <tr>
      <th class="category" colspan="6">
        {{$pagination.total}} {{tr}}CTriggerMark{{/tr}} {{tr}}found{{/tr}}
      </th>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=trigger_class}}</th>
      <td>
        <select name="trigger_class" class="str">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$trigger_classes item=_class}}
            <option value="{{$_class}}" {{if $_class == $filter->trigger_class}}selected{{/if}}>
              {{tr}}{{$_class}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>

      <th>{{mb_label object=$filter field=trigger_number}}</th>
      <td>{{mb_field object=$filter field=trigger_number canNull=true size=8}}</td>
      <th>{{mb_label object=$filter field=_date_min}}</th>
      <td>{{mb_field object=$filter field=_date_min form=Filter register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=mark}}</th>
      <td>{{mb_field object=$filter field=mark canNull=true}}</td>
      <th>{{mb_label object=$filter field=done}}</th>
      <td>{{mb_field object=$filter field=done typeEnum=select emptyLabel="All" canNull=true}}</td>
      <th>{{mb_label object=$filter field=_date_max}}</th>
      <td>{{mb_field object=$filter field=_date_max form=Filter register=true}}</td>
    </tr>

    <tr>
      <td class="button" colspan="6">
        <button class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>


