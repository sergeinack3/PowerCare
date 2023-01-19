{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="narrow">
      <input type="checkbox" checked
             onclick="this.up('table').select('input.replace_prot').invoke('writeAttribute', 'checked', this.checked);" />
    </th>
    <th class="narrow"></th>
    <th>
      {{mb_title class=CProtocoleOperatoire field=libelle}}
    </th>
    <th>
      {{tr}}Owner{{/tr}}
    </th>
  </tr>

  {{foreach from=$protocoles_op item=_protocole_op}}
  <tr>
    <td>
      <input type="checkbox" class="replace_prot" value="{{$_protocole_op->_id}}" checked />
    </td>
    <td>
      <button type="button" class="edit notext" onclick="ProtocoleOp.edit('{{$_protocole_op->_id}}');">{{tr}}Edit{{/tr}}</button>
    </td>
    <td>
      {{mb_value object=$_protocole_op field=libelle}}
    </td>
    <td>
      {{if $_protocole_op->chir_id}}
        {{$_protocole_op->_ref_chir->_view}}
      {{elseif $_protocole_op->function_id}}
        {{$_protocole_op->_ref_function->_view}}
      {{else}}
        {{$_protocole_op->_ref_group->_view}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="3">
      {{tr}}CProtocoleOperatoire.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>