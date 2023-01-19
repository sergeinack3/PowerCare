{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{mb_include module=system template=inc_pagination total=$total_interv current=$page change_page='Traceability.changePageRadio'}}

  <tr>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th>
      {{mb_label class="CPatient" field="_IPP"}}
    </th>
    <th>
      {{mb_label class="CPatient" field="_poids"}}
    </th>
    <th>
      {{mb_label class="CPatient" field="_taille"}}
    </th>
    <th>{{tr}}COperation{{/tr}}</th>
    <th>
      {{tr}}CAmpli{{/tr}}
    </th>
    <th>
      {{mb_label class="COperation" field="chir_id" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
    <th>
      {{mb_colonne class="COperation" field="dose_recue_scopie" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
    <th>
      {{mb_colonne class="COperation" field="temps_rayons_x" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
    <th>
      {{mb_colonne class="COperation" field="dose_recue_graphie" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
    <th>
      {{mb_colonne class="COperation" field="pds" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
    <th>
      {{mb_colonne class="COperation" field="kerma" order_col=$order_col order_way=$order_way function="Traceability.changeFilterRadio"}}
    </th>
  </tr>

  {{foreach from=$operations item=_operation}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_ref_patient->_guid}}');">
          {{$_operation->_ref_patient->_view}}
        </span>
      </td>
      <td>{{$_operation->_ref_patient->_IPP}}</td>
      <td>{{$_operation->_ref_patient->_poids}}</td>
      <td>{{$_operation->_ref_patient->_taille}}</td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
          {{$_operation->libelle}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_ref_ampli->_guid}}')">
          {{$_operation->_ref_ampli->_view}}
        </span>
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
      </td>
      <td>
        {{if $_operation->dose_recue_scopie}}
          {{mb_value object=$_operation field=dose_recue_scopie}} {{mb_value object=$_operation field=unite_rayons_x}}
        {{/if}}
      </td>
      <td>
          {{$_operation->temps_rayons_x }}
      </td>
      <td>
        {{if $_operation->dose_recue_graphie}}
          {{mb_value object=$_operation field=dose_recue_graphie}} {{mb_value object=$_operation field=unite_rayons_x}}
        {{/if}}
      </td>
      <td>
        {{if $_operation->pds}}
          {{mb_value object=$_operation field=pds}} {{mb_value object=$_operation field=unite_pds}}
        {{/if}}
      </td>
      <td>
        {{if $_operation->kerma}}
          {{mb_value object=$_operation field=kerma}} {{tr}}COperation.unite_kerma.mGy{{/tr}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
