{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_echange_soap current=$page change_page='EchangeSOAP.changePage' jumper='10'}}

<table class="tbl">
  <tr>
    <th></th>
    <th style="width:0.1px;"></th>
    <th>{{mb_title object=$echange_soap field="echange_soap_id"}}</th>
    <th>{{mb_title object=$echange_soap field="date_echange"}}</th>
    <th>{{mb_title object=$echange_soap field="emetteur"}}</th>
    <th>{{mb_title object=$echange_soap field="destinataire"}}</th>
    <th>{{mb_title object=$echange_soap field="type"}}</th>
    <th>{{mb_title object=$echange_soap field="web_service_name"}}</th>
    <th>{{mb_title object=$echange_soap field="function_name"}}</th>
    <th>{{mb_title object=$echange_soap field="input"}}</th>
    <th>{{mb_title object=$echange_soap field="output"}}</th>
    <th>{{mb_title object=$echange_soap field="response_time"}}</th>
    <th>{{mb_title object=$echange_soap field="trace"}}</th>
  </tr>
  {{foreach from=$echangesSoap item=_echange_soap}}
    <tbody id="echange_{{$_echange_soap->_id}}">
      {{mb_include template="inc_echange_soap" object=$_echange_soap}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="16" class="empty">
        {{tr}}CEchangeSOAP.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>