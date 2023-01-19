{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      {{$protocoles|@count}} {{if $protocoles|@count > 1}}{{tr}}CProtocole|pl{{/tr}}{{else}}{{tr}}CProtocole{{/tr}}{{/if}}
    </th>
  </tr>

  <tr>
    <th class="narrow">
      <input type="checkbox" onclick="this.up('table').select('input.protocole').invoke('writeAttribute', 'checked', this.checked);" />
    </th>
    <th>
      {{tr}}CProtocole{{/tr}}
    </th>
  </tr>

  {{foreach from=$protocoles item=_protocole}}
    <tr>
      <td>
        <input type="checkbox" class="protocole" value="{{$_protocole->_id}}" />
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_protocole->_guid}}');">
          {{if $_protocole->_view}}
            {{$_protocole->_view}}
          {{else}}
            {{tr}}CProtocole-No label{{/tr}}
          {{/if}}
        </span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CProtocole.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>