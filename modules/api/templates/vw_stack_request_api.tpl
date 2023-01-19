{{*
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=api script=api}}
{{mb_default var=refresh_filter value=0}}

<script type="text/javascript">
  Main.add(function () {
    Control.Tabs.create('tab_stack_request', true);
  });
</script>

{{if !$refresh_filter}}
<form name="form_class_api_synchronize" onsubmit="api.sendRequests(this);">
  <input type="hidden" name="m" value="api" />
  <input type="hidden" name="refresh_filter" value="1" />
  <fieldset>
    <table class="form">
      <tr></tr>
      <tr>
        {{assign var=colspan_title value=$requests|@count}}
        <th class="title" colspan="{{$colspan_title+4}}">{{tr}}CAPITiers-synchronize api{{/tr}}</th>
      </tr>
      <tr>
        <th>{{tr}}CAPITiersStackRequest-msg-choice classes to synchronize{{/tr}}</th>
        {{foreach from=$requests key=_api_name item=_requests}}
          <td>
            <label for="{{$_api_name}}_choice">{{tr}}{{$_api_name}}{{/tr}}</label>
            <input type="checkbox" name="choice_api_{{$_api_name}}" value="{{$_api_name}}" id="form_class_api_synchronize_{{$_api_name}}_choice" checked/>
          </td>
        {{/foreach}}
        <td colspan="3"><button class="btn" type="button" onclick="this.form.onsubmit();">{{tr}}CAPITiers-msg-sync{{/tr}}</button></td>
      </tr>

      <tr>
        <th class="title" colspan="8">{{tr}}CAPITiersStackRequest-title-filters requests{{/tr}}</th>
      </tr>

      <tr>
        <th>{{mb_label object=$request field="datetime_start"}}</th>
        <td>{{mb_field object=$request field="datetime_start" canNull=true register=true prop=dateTime form="form_class_api_synchronize"}}</td>
        <th>{{mb_label object=$request field="datetime_end"}}</th>
        <td colspan="3">{{mb_field object=$request field="datetime_end" canNull=true register=true prop=dateTime form="form_class_api_synchronize"}}</td>
      </tr>

      <tr>
        <th>
          {{mb_label object=$request field="group_id"}}
        </th>
        <td style="width: 35%" class="me-text-align-left">
            {{mb_field object=$request field="group_id" canNull=true form="form_class_api_synchronize" autocomplete="true,1,50,true,true"
            placeholder="Tous les établissements"}}
        </td>
        <th>{{mb_label object=$request field="constant_code"}}</th>
        <td>{{mb_field object=$request field="constant_code" canNull=true form="form_class_api_synchronize" autocomplete="true,0,50,true,true,1"
              placeholder="Nom de la constante"}}
        </td>

        <th>{{tr}}CPatient-nom-court{{/tr}}</th>
        <td>{{mb_field object=$patient_user_api field="patient_id" canNull=true form="form_class_api_synchronize" autocomplete="true,1,50,true,true"
            placeholder="Nom du patient"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$request field="emetteur"}}</th>
        <td colspan="7">
          <select name="emetteur">
            <option value="">{{tr}}Choose{{/tr}}</option>
            <option value="1">{{tr}}Yes{{/tr}}</option>
            <option value="0">{{tr}}No{{/tr}}</option>
          </select>
        </td>
      </tr>

      <tr>
        <td></td>
        <td colspan="5">
          <button type="button" class="button fas fa-filter" onclick="api.filterRequests(this.form)">{{tr}}Filter{{/tr}}</button>
          <button type="button" class="button cancel" onclick="api.clearFiler(this.form)">{{tr}}Clear{{/tr}}</button>
        </td>
      </tr>

    </table>
  </fieldset>
</form>
{{/if}}

<div id="refresh_tabs">
  <table class="tbl">
    <tr>
      <td>
        <ul id="tab_stack_request" class="control_tabs small">
            {{foreach from=$requests key=_api_name item=_requests}}
              <li><a href="#requests_{{$_api_name}}">{{tr}}{{$_api_name}}{{/tr}}</a></li>
            {{/foreach}}
        </ul>
      </td>
    </tr>
  </table>

{{foreach from=$requests key=_api_name item=_requests}}
  <div id="requests_{{$_api_name}}" style="display:none;">
    {{mb_include module="api" template="inc_tab_requests"}}
  </div>
{{/foreach}}
</div>
