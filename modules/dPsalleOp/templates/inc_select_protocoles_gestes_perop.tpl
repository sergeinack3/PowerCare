{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    $("ProtocolesGestePerop").fixedTableHeaders();
  })
</script>

<div id="ProtocolesGestePerop">
  <table class="main tbl">
    <tbody>
    {{foreach from=$protocoles_geste_perop item=_protocole}}
      <tr class="{{if !$_protocole->actif}}hatching{{/if}}">
        <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_protocole->_guid}}');">
            {{mb_value object=$_protocole field=libelle}}
          </span>
        </td>
        <td class="text">
          {{mb_value object=$_protocole field=description}}
        </td>
        <td class="narrow button">
          <button type="button"
                  onclick="SurveillancePerop.openProtocoleItems('{{$_protocole->_id}}', '{{$operation_id}}', '{{$type}}');">
            <i class="fas fa-check" style="color: forestgreen;"></i> {{tr}}common-action-Select{{/tr}}
          </button>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">
          {{tr}}CProtocoleGestePerop.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
    <thead>
    <tr>
      <th class="title" colspan="8">
        {{tr}}CProtocoleGestePerop-List of the protocols of geste perop|pl{{/tr}} ({{$protocoles_geste_perop|@count}})
      </th>
    </tr>
    <tr>
      <th class="text">{{mb_label class=CGestePerop field=libelle}}</th>
      <th class="text">{{mb_label class=CGestePerop field=description}}</th>
      <th class="narrow">{{tr}}common-Action{{/tr}}</th>
    </tr>
    </thead>
  </table>
</div>
