{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=light value=false}}

<tr>
  <td class="narrow">
      {{if $exchange->_self_sender}}
        <i class="fa fa-arrow-right" style="color: green;" title="{{tr}}CExchangeTransportLayer-message sent{{/tr}}"></i>
      {{else}}
        <i class="fa fa-arrow-left" style="color: blue;" title="{{tr}}CExchangeTransportLayer-message received{{/tr}}"></i>
      {{/if}}
  </td>
    {{if !$light}}
      <td class="narrow">
          {{mb_include module=system template=inc_object_notes object=$exchange float="none"}}
      </td>
    {{/if}}
  <td class="narrow">
    <button {{if $exchange->purge}}disabled{{/if}} type="button"
            onclick="ExchangeDataFormat.viewExchangeTransport('{{$exchange->_guid}}')" class="search">
        {{$exchange->_id}}
    </button>

    <button class="fa fa-share notext" style="color: green !important;"
            onclick="ExchangeDataFormat.sendMessage('{{$exchange->_guid}}')"
            type="button" title="{{tr}}Send{{/tr}}">
    </button>
  </td>
  <td class="narrow button">
    <a target="_blank"
       href="?m=eai&a=download_exchange_transport&exchange_guid={{$exchange->_guid}}&dialog=1&suppressHeaders=1&input=1"
       class="far fa-save notext" style="font-size: large;"></a>
  </td>

    {{if $exchange|instanceof:'Ox\Mediboard\System\CExchangeHTTP'}}
      <td class="narrow" style="text-align: center;">
        <label title='{{mb_value object=$exchange field="status_code"}}'>
            {{mb_value object=$exchange field="status_code" format=relative}}
        </label>
      </td>
    {{/if}}

  <td class="narrow">
    <label title='{{mb_value object=$exchange field="date_echange"}}'>
        {{mb_value object=$exchange field="date_echange" format=relative}}
    </label>
  </td>

  <td class="narrow">
    <label title='{{mb_value object=$exchange field="response_datetime"}}'>
        {{mb_value object=$exchange field="response_datetime" format=relative}}
    </label>
  </td>
  <td style="text-align: right;"
      class="narrow {{if $exchange->response_time > 10000}}error
      {{elseif $exchange->response_time > 1000}}warning
      {{elseif $exchange->response_time < 100}}ok{{/if}}">
      {{$exchange->response_time|round:0}} ms
  </td>

  <td class="text">
      {{assign var=source value=$exchange->_ref_source}}
      {{if $source}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$source->_guid}}')">
        <i class="fa fa-sitemap"></i>
      </span>
      {{/if}}
  </td>
  <td class="text exchange-sender">
      {{if $exchange->_self_sender}}
        <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
      {{else}}
          {{$exchange->emetteur}}
      {{/if}}
  </td>
  <td class="text exchange-receiver">
      {{if $exchange->_self_receiver}}
        <i class="fa fa-laptop" style="font-size: large;" title="[SELF]"></i>
      {{else}}
          {{$exchange->destinataire}}
      {{/if}}
  </td>
    {{if !$light}}
      <td  {{if ($exchange|instanceof:'Ox\Mediboard\System\CExchangeHTTP' && $exchange->http_fault) ||
      ($exchange|instanceof:'Ox\Interop\Ftp\CExchangeFTP' && $exchange->ftp_fault)}}class="error"{{/if}}>
          {{mb_value object=$exchange field="function_name"}}
      </td>
    {{/if}}
</tr>
