{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$operation->_ref_protocoles_operatoires item=_protocole_operatoire}}
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CProtocoleOperatoire{{/tr}} {{$_protocole_operatoire->_view}}
      </th>
    <tr>
      <th class="category">
        {{mb_title object=$_protocole_operatoire field=description_installation_patient}}
      </th>
      <th class="category">
        {{mb_title object=$_protocole_operatoire field=description_preparation_patient}}
      </th>
    </tr>
    <tr>
      <td class="halfPane">
        {{mb_value object=$_protocole_operatoire field=description_installation_patient}}
      </td>
      <td>
        {{mb_value object=$_protocole_operatoire field=description_preparation_patient}}
      </td>
    </tr>
  </table>
{{/foreach}}
