{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="3"></th>
    <th>{{mb_title class=CExchangeTransportLayer field="date_echange"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="response_datetime"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="response_time"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="source_id"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="emetteur"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="destinataire"}}</th>
  </tr>
  {{foreach from=$exchanges_tl key=_exchange_classname item=_exchanges}}
    <tr>
      <th class="section" colspan="13">
        {{tr}}{{$_exchange_classname}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$_exchanges item=_exchange}}
      <tbody id="echange_{{$_exchange->_id}}">
      {{mb_include template="inc_exchange_transport" exchange=$_exchange light=true}}
      </tbody>
      {{foreachelse}}
      <tr>
        <td colspan="17" class="empty">
          {{tr}}{{$_exchange_classname}}.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>