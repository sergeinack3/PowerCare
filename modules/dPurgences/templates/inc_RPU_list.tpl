{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<form name="pagination">
  <input name="order_col" value="{{$order_col}}" hidden>
  <input name="order_way" value="{{$order_way}}" hidden>
  <input name="page" value="{{$page}}" hidden>
</form>

<table class="main tbl">
  <tr>
    <td colspan="9">
        {{mb_include module=system template=inc_pagination total=$total current=$page change_page='RPUDashboard.changePage' step=$step}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CPatient-_IPP{{/tr}}</th>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th>{{tr}}CSejour-_NDA{{/tr}}</th>
    <th>{{mb_colonne class=CSejour field=entree order_col=$order_col order_way=$order_way function='RPUDashboard.changeSort'}}</th>
    <th>{{tr}}CSejour-sortie{{/tr}}</th>
    <th class="me-text-align-center">{{mb_colonne class=CRPU field=_count_extract_passages order_col=$order_col order_way=$order_way function='RPUDashboard.changeSort'}}</th>
    <th>{{mb_colonne class=CRPU field=_first_extract_passages order_col=$order_col order_way=$order_way function='RPUDashboard.changeSort'}}</th>
    <th>{{tr}}CRPU-_last_extract_passages-court{{/tr}}</th>
  </tr>
    {{foreach from=$rpus item=_rpu}}
      <tr>
        <td>
            {{mb_value object=$_rpu->_ref_sejour->_ref_patient field=_IPP}}
        </td>
        <td onmouseover="ObjectTooltip.createEx(this, '{{$_rpu->_ref_sejour->_ref_patient->_guid}}')">
            {{$_rpu->_ref_sejour->_ref_patient}}
        </td>
        <td>
            {{$_rpu->_ref_sejour->_NDA_view}}
        </td>
        <td>
            {{mb_value object=$_rpu->_ref_sejour field=entree}}
        </td>
        <td>
            {{mb_value object=$_rpu->_ref_sejour field=sortie}}
        </td>
        <td class="me-text-align-center">
            {{$_rpu->_ref_extract_passages|@count}}
        </td>
        <td>
            {{if $_rpu->_first_extract_passages}}
              {{mb_value object=$_rpu->_first_extract_passages field=date_extract}}
            {{/if}}
        </td>
        <td>
            {{if $_rpu->_last_extract_passages}}
              {{mb_value object=$_rpu->_last_extract_passages field=date_extract}}
            {{/if}}
        </td>
      </tr>
    {{foreachelse}}
        <tr>
          <td class="empty">
            {{tr}}CRPU.none{{/tr}}
          </td>
        </tr>
    {{/foreach}}
</table>
