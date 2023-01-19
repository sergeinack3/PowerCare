{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="narrow">
      {{tr}}CProtocole-Nb of usages{{/tr}}
    </th>
    <th class="narrow">
      {{tr}}CMbFieldSpec.type.pct{{/tr}}
    </th>
    <th>
      {{tr}}CProtocole{{/tr}}
    </th>
    <th class="narrow"></th>
  </tr>

  <tr style="font-weight: bold;">
    <td style="text-align: right;">
      {{$count_total|integer}}
    </td>
    <td style="text-align: right;">
      {{1|percent}}
    </td>
    <td>
      {{tr}}Total{{/tr}}
    </td>
    <td></td>
  </tr>

  {{foreach from=$results item=_result}}
    {{assign var=_protocole_id value=$_result.protocole_id}}
    {{assign var=_protocole value=$protocoles.$_protocole_id}}
    <tr>
      <td style="text-align: right;">
        {{$_result.count|integer}}
      </td>
      <td style="text-align: right;">
        {{$_result.percent|percent}}
      </td>
      <td class="text">
        {{$_protocole->_view}}
      </td>
      <td>
        <button class="search notext" onclick="StatProtocole.detailSejours('{{$_protocole->_id}}');">{{tr}}Details{{/tr}}</button>
      </td>
    </tr>
  {{/foreach}}
</table>