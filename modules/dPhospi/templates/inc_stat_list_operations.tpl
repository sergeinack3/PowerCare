{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("list_operations");
  });
</script>

<ul id="list_operations" class="control_tabs">
  <li>
    <a href="#prevues" {{if !$operations_prevues|@count}}class="empty"{{/if}}>
      Prévues
      <small>({{$operations_prevues|@count}})</small>
    </a>
  </li>
  <li>
    <a href="#placees" {{if !$operations_placees|@count}}class="empty"{{/if}}>
      Placées
      <small>({{$operations_placees|@count}})</small>
    </a>
  </li>
</ul>

<div id="prevues" style="display: none;">
  <table class="tbl">
    <tr>
      <th class="title" colspan="6">
        Liste des interventions
      </th>
    </tr>
    {{foreach from=$operations_prevues item=_operation}}
      {{mb_include module=hospi template=inc_line_stat_operation}}
      {{foreachelse}}
      <tr>
        <td class="empty">
          {{tr}}COperation.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="placees" style="display: none;">
  <table class="tbl">
    <tr>
      <th class="title" colspan="6">
        Liste des interventions
      </th>
    </tr>
    {{foreach from=$operations_placees item=_operation}}
      {{mb_include module=hospi template=inc_line_stat_operation}}
      {{foreachelse}}
      <tr>
        <td class="empty">
          {{tr}}COperation.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>
