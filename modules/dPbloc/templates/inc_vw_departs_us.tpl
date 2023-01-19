{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>
    {{mb_colonne class=COperation field=_heure_us order_col=$order_col order_way=$order_way function="refreshListOperations"}}
  </th>
  <th>
    {{mb_colonne class=CPatient field=nom order_col=$order_col order_way=$order_way function="refreshListOperations"}}
  </th>
  <th>
    {{mb_colonne class=COperation field=time_operation order_col=$order_col order_way=$order_way function="refreshListOperations"}}
  </td>
  <th>
    {{mb_colonne class=COperation field=salle_id order_col=$order_col order_way=$order_way function="refreshListOperations"}}
  </th>
  <th>
    {{mb_label class=CLit field=chambre_id}}
  </th>
</tr>
{{foreach from=$operations item=_operation}}
  {{assign var=sejour  value=$_operation->_ref_sejour}}
  {{assign var=patient value=$sejour->_ref_patient}}
  {{assign var=salle   value=$_operation->_ref_salle}}
  {{assign var=affectation value=$sejour->_ref_curr_affectation}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
        {{mb_value object=$_operation field=_heure_us}}
      </span>
    </td>
    <td>
      {{mb_include module=system template=inc_vw_mbobject object=$patient}}
    </td>
    <td>
      {{mb_value object=$_operation field=time_operation}}
    </td>
    <td>
      {{mb_include module=system template=inc_vw_mbobject object=$salle}}
    </td>
    <td>
      {{mb_include module=system template=inc_vw_mbobject object=$affectation}}
    </td>
  </tr>
{{foreachelse}}
  <tr>
    <td class="empty" colspan="5">{{tr}}COperation.none{{/tr}}</td>
  </tr>
{{/foreach}}