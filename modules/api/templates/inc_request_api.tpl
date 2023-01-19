{{*
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="request_id_{{$_request->_id}}">
  <td>
    <input type="checkbox" name="request_checkbox" {{if !$_request->acquittement && !$_request->emptied}}class="data_no_send"{{/if}} id="{{$_request->_id}}">
  </td>
  <td style="min-width: 10px">
      {{if $_request->emetteur}}
        <i class="fa fa-arrow-right" style="color: green"></i>
      {{else}}
        <i class="fa fa-arrow-left" style="color: darkorange"></i>
      {{/if}}
  </td>
  <td class="right">
      {{if $_request->acquittement}}
        <button type="button" class="button search" onclick="api.modalResponseRequest(this.form, '{{$_request->_id}}')">
            {{mb_value object=$_request field=api_tiers_stack_request_id}}
        </button>
      {{elseif !$_request->acquittement && !$_request->emptied}}
        {{mb_value object=$_request field=api_tiers_stack_request_id}}
      {{/if}}
  </td>
  <td class="narrow">
    {{if $_request->acquittement}}
      <button type="button" class="button notext fas fa-sync" style="color: #0a6dcf!important;" onclick="api.sendSelectedRequest('{{$_api_name}}', '{{$_request->_id}}')"></button>
    {{elseif !$_request->acquittement && !$_request->emptied}}
      <button type="button" class="button notext send" onclick="api.sendSelectedRequest('{{$_api_name}}', '{{$_request->_id}}')"></button>
    {{/if}}
  </td>
  <td {{if !$_request->_ref_user_api || !$_request->_ref_user_api->_id}}class="hatching warning"
      {{else}}onmouseover="ObjectTooltip.createEx(this, '{{$_request->_ref_user_api->_guid}}');"{{/if}}>
      {{$_request->api_id}}
  </td>

  <td {{if $_request->_ref_group}}onmouseover="ObjectTooltip.createEx(this, '{{$_request->_ref_group->_guid}}');"{{/if}}>
      {{if $_request->group_id}}
          {{mb_value object=$_request field=group_id}}
      {{/if}}
  </td>
  <td>{{mb_value object=$_request field=constant_code}}</td>
  <td>{{mb_value object=$_request field=scope}}</td>
  <td>{{mb_value object=$_request field=datetime_start}}</td>
  <td>{{mb_value object=$_request field=datetime_end}}</td>
  <td>{{mb_value object=$_request field=send_datetime}}</td>
  <td>{{mb_value object=$_request field=receive_datetime}}</td>
  <td>{{mb_value object=$_request field=agregate}}</td>
  <td>{{mb_value object=$_request field=max_attemp}}</td>
  <td>{{mb_value object=$_request field=nb_request}}</td>
  <td>{{mb_value object=$_request field=time_response}}</td>
  <td>{{mb_value object=$_request field=nb_stored}}</td>
</tr>