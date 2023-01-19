{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=exchange_data_format ajax=true}}

<table class="tbl">
  <tr>
    <th colspan="3"></th>
      {{if $exchange|instanceof:'Ox\Mediboard\System\CExchangeHTTP'}}
        <th>{{mb_title class=CExchangeHttp field="status_code"}}</th>
      {{/if}}
    <th>{{mb_title class=CExchangeTransportLayer field="date_echange"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="response_datetime"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="response_time"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="emetteur"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="destinataire"}}</th>
    <th>{{mb_title class=CExchangeTransportLayer field="source_id"}}</th>
  </tr>
    {{foreach from=$exchanges item=_exchange}}
      <tbody id="echange_{{$_exchange->_id}}">
      {{mb_include template="inc_exchange_transport" exchange=$_exchange light=true}}
      </tbody>
    {{/foreach}}
</table>