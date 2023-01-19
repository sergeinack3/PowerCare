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

{{mb_include module=system template=inc_pagination total=$total current=$page change_page='GestePerop.changePageProtocole' step=$step}}

<div id="ProtocolesGestePerop">
  <table class="main tbl">
    <tbody>
    {{foreach from=$protocoles_geste_perop item=_protocole}}
      <tr class="{{if !$_protocole->actif}}hatching{{/if}}">
        <td class="text">
          {{mb_value object=$_protocole field=libelle}}
        </td>
        <td class="text">
          {{mb_value object=$_protocole field=description}}
        </td>
        <td>
          {{mb_value object=$_protocole field=group_id tooltip=true}}
        </td>
        <td>
          {{mb_value object=$_protocole field=function_id tooltip=true}}
        </td>
        <td>
          {{mb_value object=$_protocole field=user_id tooltip=true}}
        </td>
        <td class="button narrow">
          {{$_protocole->_count_items}}
        </td>
        <td class="button">
          {{mb_include module="system" template="inc_form_button_active" field_name="actif" object=$_protocole
          onComplete="GestePerop.loadProtocolesGestesPerop(getForm('filterProtocolesGestePerop'));"}}
        </td>
        <td class="narrow button">
          <button type="button" title="{{tr}}Modify{{/tr}}"
                  onclick="GestePerop.editProtocoleGestePerop('{{$_protocole->_id}}');">
            <i class="fas fa-edit"></i>
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

        <button style="float: left;" type="button" class="me-primary" onclick="GestePerop.editProtocoleGestePerop(0);">
          <i class="fas fa-plus"></i> {{tr}}CProtocoleGestePerop-action-Create a protocole{{/tr}}
        </button>
      </th>
    </tr>
    <tr>
      <th class="text">{{mb_label class=CGestePerop field=libelle}}</th>
      <th class="text">{{mb_label class=CGestePerop field=description}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=group_id}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=function_id}}</th>
      <th class="narrow">{{mb_label class=CGestePerop field=user_id}}</th>
      <th class="text narrow">{{tr}}CProtocoleGestePerop-Associated item|pl{{/tr}}</th>
      <th class="narrow">{{mb_label class=CProtocoleGestePerop field=actif}}</th>
      <th class="narrow">{{tr}}common-Action{{/tr}}</th>
    </tr>
    </thead>
  </table>
</div>
