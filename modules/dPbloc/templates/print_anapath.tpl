{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$blocs item=bloc key=bloc_id}}
  <table class="tbl">
    <tr class="clear">
      <th colspan="100">
        <h1>
          <a href="#" onclick="window.print()">
            Anapath du {{$date|date_format:$conf.date}}
            -
            {{$bloc}}
            -
            {{$operations_tab.$bloc_id|@count}} prélèvements
          </a>
        </h1>
      </th>
    </tr>
    <tr>
      <th>{{mb_title class=COperation field=time_operation}}</th>
      <th>{{mb_title class=COperation field=libelle}}</th>
      <th>{{mb_title class=CSejour field=patient_id}}</th>
      <th>{{mb_title class=COperation field=chir_id}}</th>
      <th>{{mb_title class=COperation field=salle_id}}</th>
      <th>{{mb_title class=COperation field=flacons_anapath}}</th>
      <th>{{mb_title class=COperation field=description_anapath}}</th>
      <th>{{mb_title class=COperation field=labo_anapath_id}}</th>
    </tr>
    {{foreach from=$operations_tab.$bloc_id item=_op}}
    <tr>
      <td>{{mb_value object=$_op field=_datetime_best}}</td>
      <td class="text">{{mb_include module=planningOp template=inc_vw_operation _operation=$_op}}</td>
      <td class="text">{{mb_include module=system template=inc_vw_mbobject object=$_op->_ref_patient}}</td>
      <td class="text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}</td>
      <td class="text">{{mb_include module=system template=inc_vw_mbobject object=$_op->_ref_salle}}</td>
      <td class="button">{{mb_value object=$_op field=flacons_anapath}}</td>
      <td class="text">{{mb_value object=$_op field=description_anapath}}</td>
      <td class="text">{{mb_value object=$_op field=labo_anapath_id}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">
        {{tr}}COperation.none{{/tr}}
      </td>
    </tr>
    {{/foreach}}
  </table>
{{/foreach}}
