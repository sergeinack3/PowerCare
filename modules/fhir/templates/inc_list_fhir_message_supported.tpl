{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="form_manage_fhir_messages_supported" id="form_manage_fhir_messages_supported">
  <table class="tbl">
    <tr>

    </tr>

    {{foreach from=$actor_messages key=actor_guid item=messages}}
      {{assign var=actor value=$actors.$actor_guid}}
      <tr>
        <th style="cursor: pointer"
            colspan="5"
            onmouseover="ObjectTooltip.createEx(this, '{{$actor_guid}}')">
          {{$actor->_view}}
        </th>
      </tr>
      {{foreach from=$messages item=message}}
        <tr onmouseover="ObjectTooltip.createEx(this, '{{$message->_guid}}')" style="cursor: pointer">
          <td class="narrow">
            <input type="checkbox" name="message_ids[]" value="{{$message->_id}}" checked="checked"/>
          </td>
          <td>{{$message->message}}</td>
          <td>{{$message->profil}}</td>
          <td>{{$message->transaction}}</td>
          <td>{{$message->active}}</td>
        </tr>
      {{/foreach}}
    {{/foreach}}

    <tr>
      <td colspan="5">
        <button type="button" name="send" onclick="new Url('fhir', 'fhir_purge_messages_supported').addFormData(this.form).requestUpdate('form_manage_fhir_messages_supported')">
          {{tr}}Delete-all{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
