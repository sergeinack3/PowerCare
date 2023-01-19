{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=count_active value=$infos_service|@count}}
{{if $show_inactive}}
  {{math assign=count_active equation='x-y' x=$count_active y=$count_inactive}}
{{/if}}
<script>
  Main.add(function () {
    InfoGroup.setTabInformation('{{$count_active}}');
  });
</script>

<table class="tbl">
  <thead>
  <tr>
    <th class="narrow">
      {{mb_title class=CInfoGroup field=date}}
    </th>
    <th rowspan="2">
      {{mb_title class=CInfoGroup field=patient_id}}
    </th>
    <th rowspan="2">
      {{mb_title class=CInfoGroup field=type_id}}
    </th>
    <th colspan="3" rowspan="2">
      <button type="button" class="new notext me-primary" style="float:right" onclick="InfoGroup.addInfoService()">
        {{tr}}New{{/tr}}
      </button>
      <label style="float:right">
        <input type="checkbox" name="show_inactive" {{if $show_inactive}}checked="checked"{{/if}}
               onclick="InfoGroup.infoServiceShowInactive((this.checked) ? 1 : 0)" />
        {{tr}}CInfoGroup-action-Show inactive|pl{{/tr}} ({{$count_inactive}})
      </label>
      {{mb_title class=CInfoGroup field=description}}
    </th>
  </tr>
  <tr>
    <th>
      {{mb_title class=CInfoGroup field=user_id}}
    </th>
  </tr>
  </thead>
  {{foreach from=$infos_service item=_info_service}}
    <tr class="{{if $_info_service->actif == 0}}opacity-50{{/if}}">
      <td style="text-align:center">{{mb_ditto name=date value=$_info_service->date|date_format:$conf.datetime}}</td>
      <td{{if !$_info_service->patient_id}} class="empty"{{/if}} rowspan="2" style="text-align:center">
        {{if $_info_service->patient_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_info_service->_ref_patient->_guid}}')" style="margin:auto">
            {{$_info_service->_ref_patient}}
          </span>
        {{else}}
          {{tr}}CPatient.none{{/tr}}
        {{/if}}
      </td>
      <td rowspan="2">{{mb_value object=$_info_service field=type_id}}</td>
      <td rowspan="2">{{mb_value object=$_info_service field=description}}</td>
      <td class="narrow" rowspan="2">
        <button type="button" class="edit notext"
                onclick="InfoGroup.editInfoService({{$_info_service->_id}})" title="{{tr}}Edit{{/tr}}"></button>
      </td>
      <td class="narrow" rowspan="2">{{mb_include module=system template=inc_object_history object=$_info_service}}</td>
    </tr>
    <tr class="{{if $_info_service->actif == 0}}opacity-50{{/if}}">
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_info_service->_ref_user}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CInfoGroup-Service information none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
