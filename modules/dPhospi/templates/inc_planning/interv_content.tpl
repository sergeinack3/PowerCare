{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Intervention -->
<td class="text">
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    {{$curr_operation->_datetime|date_format:$conf.date}}
    {{if $curr_operation->time_operation != "00:00:00"}}
      {{tr}}To{{/tr}} {{$curr_operation->time_operation|date_format:$conf.time}}
    {{/if}}
    <br />
  {{/foreach}}
</td>
<td class="text">
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    <ul style="padding-left: 0px;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_operation->_guid}}');">
          {{if $curr_operation->libelle}}
            <em>[{{$curr_operation->libelle}}]</em>
            <br />
            {{else}}
            {{foreach from=$curr_operation->_ext_codes_ccam item=curr_code}}
            <em>{{$curr_code->code}}</em>
            {{if $filter->_ccam_libelle}}
              : {{$curr_code->libelleLong|truncate:60:"...":false}}
              <br />
            {{else}}
              ;
            {{/if}}
          </span>
      {{/foreach}}
      {{/if}}
    </ul>
  {{/foreach}}
</td>
<td>
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    {{$curr_operation->cote|truncate:1:""|capitalize}}
    <br />
  {{/foreach}}
</td>
<td class="text">
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    {{$curr_operation->examen|nl2br}}
    <br />
  {{/foreach}}
</td>
<td class="text compact">
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    {{$curr_operation->rques|nl2br}}
    <br />
  {{/foreach}}
</td>