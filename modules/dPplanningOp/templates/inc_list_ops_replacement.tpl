{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $operations|@count}}
  <div class="small-info">
    {{tr var1=$operations|@count}}COperation-Operations are planned with old product{{/tr}}
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th class="narrow">
      <input type="checkbox" checked
             onclick="this.up('table').select('input.replace_op').invoke('writeAttribute', 'checked', this.checked);" />
    </th>
    <th>
      {{tr}}COperation{{/tr}}
    </th>
  </tr>

  {{foreach from=$operations item=_operation}}
  <tr>
    <td>
      <input type="checkbox" class="replace_op" value="{{$_operation->_id}}" checked />
    </td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">
        {{$_operation->_view}}
      </span>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="2">{{tr}}COperation.none{{/tr}}</td>
  </tr>
  {{/foreach}}

  <tr>
    <td class="button" colspan="2">
      {{if $operations|@count}}
        <button type="button" class="tick" onclick="ProtocoleOp.validerReplacement(1, Control.Modal.refresh);">{{tr}}CProtocoleOperatoire-Validate replacement{{/tr}}</button>
      {{/if}}
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>