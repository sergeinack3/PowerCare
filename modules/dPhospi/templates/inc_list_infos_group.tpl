{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="7">
      {{tr}}CInfoGroup-List of information|pl{{/tr}}
      <span style="float: right">
      <label>
        <input type="checkbox" name="show_inactive" {{if $show_inactive}}checked="checked"{{/if}}
               onclick="InfoGroup.loadListInfosGroup((this.checked) ? 1 : 0)" />
        {{tr}}CInfoGroup-action-Show inactive|pl{{/tr}} ({{$count_inactive}})
      </label>
    </span>
    </th>
  </tr>
  <tr>
    <th>{{mb_title class=CInfoGroup field=user_id}}</th>
    <th>{{mb_title class=CInfoGroup field=date}}</th>
    <th>{{mb_title class=CInfoGroup field=patient_id}}</th>
    <th>{{mb_title class=CInfoGroup field=description}}</th>
    <th>{{mb_title class=CInfoGroup field=actif}}</th>
    <th></th>
    <th></th>
  </tr>
  {{foreach from=$infos item=_info_group}}
    <tbody id="list_info_{{$_info_group->_id}}">
    <tr class="{{if $_info_group->actif == 0}}opacity-50{{/if}}">
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_info_group->_ref_user}}
      </td>
      <td>
        {{mb_value object=$_info_group field=date}}
      </td>
      <td{{if !$_info_group->patient_id}} class="empty"{{/if}}>
        {{if $_info_group->patient_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_info_group->_ref_patient->_guid}}')">
            {{$_info_group->_ref_patient}}
          </span>
        {{else}}
          {{tr}}CPatient.none{{/tr}}
        {{/if}}
      </td>
      <td style="width: 50%;">
        {{mb_value object=$_info_group field="description"}}
      </td>
      <td>
        {{mb_value object=$_info_group field=actif}}
      </td>
      <td class="narrow">
        <button type="button" class="edit notext" onclick="InfoGroup.editInfoGroup('{{$_info_group->_id}}');"></button>
      </td>
      <td>
        {{mb_include module=system template=inc_object_history object=$_info_group}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">
        {{tr}}CInfoGroup.none{{/tr}}
      </td>
    </tr>
    </tbody>
  {{/foreach}}
</table>