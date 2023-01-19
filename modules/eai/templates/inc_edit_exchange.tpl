{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=form_name value="editExchange_`$exchange->_id`"}}
<form name="{{$form_name}}" method="post" onsubmit="return onSubmitFormAjax(this, function () {
                                                      ExchangeDataFormat.refreshExchange('{{$exchange->_guid}}');
                                                      Control.Modal.close();
                                                      })">
  {{mb_key   object=$exchange}}
  {{mb_class object=$exchange}}
  {{mb_field object=$exchange field="reprocess" hidden=true}}
  <table class="form">
    <tr>
      <th class="title modify text" colspan="3">
        {{mb_include module=system template=inc_object_notes object=$exchange}}

        {{tr}}{{$exchange->_class}}-title-modify{{/tr}} '{{$exchange->_view}}'
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$exchange field="date_production"}}</th>
      <td>{{mb_field object=$exchange field="date_production" register=true form=$form_name}}</td>
      <td>({{$exchange->date_production}})</td>
    </tr>
    <tr>
      <th>{{mb_label object=$exchange field="send_datetime"}}</th>
      <td>
        {{mb_field object=$exchange field="send_datetime" register=true form=$form_name}}
        <button type="button" class="cancel notext" onclick="$V(this.form.send_datetime, '');$V(this.form.date_echange_da, '')">
          {{tr}}cancel{{/tr}}
        </button>
      </td>
      <td>{{if $exchange->send_datetime}}({{$exchange->send_datetime}}){{/if}}</td>
    </tr>
    {{if $exchange->response_datetime}}
    <tr>
      <th>{{mb_label object=$exchange field="response_datetime"}}</th>
      <td>
        {{mb_field object=$exchange field="response_datetime" register=true form=$form_name}}
        <button type="button" class="cancel notext" onclick="$V(this.form.send_datetime, '');$V(this.form.response_datetime_da, '')">
          {{tr}}cancel{{/tr}}
        </button>
      </td>
      <td>{{if $exchange->response_datetime}}({{$exchange->response_datetime}}){{/if}}</td>
    </tr>
    {{/if}}
    {{if $exchange->_friendly_delay_send}}
      <tr>
        <th>{{mb_label object=$exchange field="_friendly_delay_send"}}</th>
        <td colspan="2">{{$exchange->_friendly_delay_send.locale}}</td>
      </tr>
    {{/if}}
    {{if $exchange->_friendly_duration_send}}
      <tr>
        <th>{{mb_label object=$exchange field="_friendly_duration_send"}}</th>
        <td colspan="2">{{$exchange->_friendly_duration_send.locale}}</td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$exchange field="message_valide"}}</th>
      <td colspan="2">{{mb_field object=$exchange field="message_valide"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$exchange field="acquittement_valide"}}</th>
      <td colspan="2">{{mb_field object=$exchange field="acquittement_valide"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$exchange field="reprocess"}} ({{mb_value object=$exchange field="reprocess"}})</th>
      <td colspan="2">
        <button type="button" class="erase oneclick" onclick="$V(this.form.reprocess, '0'); this.form.onsubmit()">
            {{tr}}Erase{{/tr}}
        </button>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$exchange field="master_idex_missing"}}</th>
      <td colspan="2">{{mb_field object=$exchange field="master_idex_missing"}}</td>
    </tr>

      {{if $object|instanceof:'Ox\Interop\Hl7\CExchangeHL7v2'}}
        <tr>
          <th>{{mb_label object=$exchange field="error_codes"}}</th>
          <td colspan="2">{{mb_value object=$exchange field="error_codes"}}</td>
        </tr>
      {{/if}}

    <tr>
      <td class="button" colspan="3">
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
