{{*
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=api script=api}}
{{if $warning.$_api_name === null}}
    {{mb_include module=system template=inc_pagination total=$total_requests.$_api_name current=$page.$_api_name
    change_page='api.changePage' jumper='10' step=50 change_page_arg=$_api_name}}
{{/if}}
<form name="{{$_api_name}}_request_response" id="{{$_api_name}}_request_response">
  <button type="button" class="send btn button" onclick="api.sendSelectedRequests('{{$_api_name}}')" title="{{tr}}CAPITiersStackRequest-msg-send all requests selected{{/tr}}">
    {{tr}}CAPITiersStackRequest-msg-send requests selected{{/tr}}
  </button>
  <table class="tbl">
    <tr>
      <th class="title" colspan="20">{{tr}}CAPITiersStackRequest{{/tr}}</th>
    </tr>
      {{if $warning.$_api_name !== null}}
        <tr>
          <td>
            <div class="small-warning">{{$warning.$_api_name}}</div>
          </td>
        </tr>
      {{else}}
          {{foreach from=$_requests item=_request name=_requests_api}}
              {{if $smarty.foreach._requests_api.first}}
                <tr>
                  <th class="narrow">
                    <button type="button" onclick="api.checkAll('{{$_api_name}}_request_response')" class="button notext fas fa-check-circle" id="select_mode_request" value="1">
                  </th>
                  <th></th>
                  <th>{{mb_title object=$_request field=api_id}}</th>
                  <th class="narrow"></th>
                  <th>{{tr}}CUserAPI{{/tr}}</th>
                  <th>{{tr}}CPatientGroup-group_id-court{{/tr}}</th>
                  <th>{{mb_label object=$_request field=constant_code}}</th>
                  <th>{{mb_label object=$_request field=scope}}</th>
                  <th>{{mb_label object=$_request field=datetime_start}}</th>
                  <th>{{mb_label object=$_request field=datetime_end}}</th>
                  <th>{{mb_label object=$_request field=send_datetime}}</th>
                  <th>{{mb_label object=$_request field=receive_datetime}}</th>
                  <th>{{mb_label object=$_request field=agregate}}</th>
                  <th>{{mb_label object=$_request field=max_attemp}}</th>
                  <th>{{mb_label object=$_request field=nb_request}}</th>
                  <th>{{mb_label object=$_request field=time_response}}</th>
                  <th>{{mb_label object=$_request field=nb_stored}}</th>
                </tr>
              {{/if}}
            {{mb_include module=api template=inc_request_api}}
          {{/foreach}}
      {{/if}}
  </table>
</form>