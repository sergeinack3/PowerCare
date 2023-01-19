{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination current=$page total=$total step=$step
change_page="ProtocoleOp.changePage" change_page_arg=$order_way}}
<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>
      {{mb_colonne class="CProtocoleOperatoire" field="libelle" order_col=$order_col order_way=$order_way
      function="ProtocoleOp.refreshList"}}
    </th>
    <th>
      {{mb_title class=Ox\Mediboard\PlanningOp\CProtocoleOperatoire field=numero_version}}
    </th>
    {{if $search_all_protocole_op}}
      <th>{{tr}}CProtocoleOperatoire-context{{/tr}}</th>
      <th>
        {{mb_colonne class="CProtocoleOperatoire" field="_ref"
        order_col=$order_col order_way=$order_way function="ProtocoleOp.refreshList"}}
      </th>
    {{/if}}
    {{if !$search_all_protocole_op}}
      <th>
        {{mb_title class=Ox\Mediboard\PlanningOp\CProtocoleOperatoire field=remarque}}
      </th>
    {{/if}}
    <th class="narrow">
      {{mb_title class=Ox\Mediboard\PlanningOp\CProtocoleOperatoire field=validation_praticien_id}}
    </th>
    <th class="narrow">
      {{mb_title class=Ox\Mediboard\PlanningOp\CProtocoleOperatoire field=validation_cadre_bloc_id}}
    </th>
  </tr>

  {{foreach from=$protocoles_op item=_protocole}}
    <tr {{if !$_protocole->actif}}class="hatching"{{/if}}>
      <td>
        <button type="button" class="edit notext" onclick="ProtocoleOp.edit('{{$_protocole->_id}}');">{{tr}}Edit{{/tr}}</button>
        <button type="button" class="print notext" onclick="ProtocoleOp.print('{{$_protocole->_id}}');">{{tr}}Print{{/tr}}</button>
      </td>
      <td>
        {{mb_value object=$_protocole field=libelle}}
      </td>
      <td>
        {{mb_value object=$_protocole field=numero_version}}
      </td>
      {{if $search_all_protocole_op}}
        <td>
          {{if $_protocole->chir_id}}
            {{tr}}CProtocoleOperatoire-chir_id{{/tr}}
          {{else}}
            {{tr}}CProtocoleOperatoire-function_id{{/tr}}
          {{/if}}
        </td>
        <td>
          {{if $_protocole->chir_id}}
            {{$_protocole->_ref_chir->_view}}
          {{else}}
            {{$_protocole->_ref_function->_view}}
          {{/if}}
        </td>
      {{/if}}
      {{if !$search_all_protocole_op}}
        <td>
          {{mb_value object=$_protocole field=remarque}}
        </td>
      {{/if}}
      <td>
        {{if $_protocole->validation_praticien_id}}
          <i class="fas fa-check texticon-ok"
             title="{{tr var1=$_protocole->validation_praticien_datetime|date_format:$conf.datetime var2=$_protocole->_ref_validation_praticien->_view}}CProtocoleOperatoire-Validation prat detail{{/tr}}"></i>
        {{else}}
          <i class="fas fa-times texticon-ko"
             title="{{tr}}CProtocoleOperatoire-Not validated prat{{/tr}}"></i>
        {{/if}}
      </td>
      <td>
        {{if $_protocole->validation_cadre_bloc_id}}
          <i class="fas fa-check texticon-ok"
             title="{{tr var1=$_protocole->validation_cadre_bloc_datetime|date_format:$conf.datetime var2=$_protocole->_ref_validation_cadre_bloc->_view}}CProtocoleOperatoire-Validation cadre bloc detail{{/tr}}"></i>
        {{else}}
          <i class="fas fa-times texticon-ko"
             title="{{tr}}CProtocoleOperatoire-Not validated cadre bloc{{/tr}}"></i>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">{{tr}}CProtocoleOperatoire.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
