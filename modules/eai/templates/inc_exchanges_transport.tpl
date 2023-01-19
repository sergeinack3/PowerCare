{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_exchanges current=$page
change_page='ExchangeDataFormat.changePage' jumper='10' step=25}}

<table class="tbl">
  <tr>
    <th colspan="4"></th>
    <th>{{mb_title object=$exchange field="date_echange"}}</th>
    <th>{{mb_title object=$exchange field="response_datetime"}}</th>
    <th>{{mb_title object=$exchange field="response_time"}}</th>
    <th>{{mb_title object=$exchange field="source_id"}}</th>
    <th>{{mb_title object=$exchange field="emetteur"}}</th>
    <th>{{mb_title object=$exchange field="destinataire"}}</th>
    <th>{{mb_title object=$exchange field="function_name"}}</th>
  </tr>
  {{foreach from=$exchanges item=_exchange}}
    <tbody id="echange_{{$_exchange->_id}}">
    {{mb_include template="inc_exchange_transport" exchange=$_exchange}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="16" class="empty">
        {{tr}}{{$exchange->_class}}.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>