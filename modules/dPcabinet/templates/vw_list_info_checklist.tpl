{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$infos item=_info}}
  <tr class="{{if !$_info->actif}}hatching{{/if}}">
    <td class="button">
      <button type="button" class="edit notext" onclick="InfoChecklist.edit('{{$_info->_id}}');"></button>
    </td>
    <td class="text">{{mb_value object=$_info field=libelle}}</td>
    <td class="text">
      {{if !$_info->function_id}}
        {{tr}}All{{/tr}}
      {{else}}
        {{mb_value object=$_info field=function_id}}
      {{/if}}
    </td>
    <td>{{mb_value object=$_info field=actif}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="4">{{tr}}CInfoChecklist.none{{/tr}}</td>
  </tr>
{{/foreach}}