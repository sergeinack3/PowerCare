{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=order_col value=$operation->_prepa_order_col}}
{{assign var=order_way value=$operation->_prepa_order_way}}
{{assign var=function value="PreparationSalle.refreshList"}}

<table class="tbl">
  <tr>
    <th class="narrow">
      <input type="checkbox" onclick="PreparationSalle.toggleCheckAll(this);" />
    </th>
    <th class="narrow"></th>
    <th style="width: 20%">{{mb_colonne class=CPatient field=nom order_col=$order_col order_way=$order_way function=$function}}</th>
    <th style="width: 20%">{{mb_colonne class=COperation field=libelle order_col=$order_col order_way=$order_way function=$function}}</th>
    <th style="width: 20%">{{mb_colonne class=COperation field=salle_id order_col=$order_col order_way=$order_way function=$function}}</th>
    <th style="width: 20%">{{mb_title class=CProtocoleOperatoireDHE field=protocole_operatoire_id}}</th>
    <th class="narrow">{{mb_colonne class=COperation field=numero_panier order_col=$order_col order_way=$order_way function=$function}}</th>
  </tr>

  {{foreach from=$operations item=_operation}}
    <tr id="panier_{{$_operation->_id}}">
      {{mb_include module=planningOp template=inc_line_op}}
    </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="7">
      {{tr}}COperation.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>