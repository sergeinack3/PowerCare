{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl me-margin-top-0 me-no-border-radius-top me-no-align">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CSupervisionTableRow field=title}}</th>
    <th>{{mb_title class=CSupervisionTableRow field=value_type_id}}</th>
    <th>{{mb_title class=CSupervisionTableRow field=value_unit_id}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$rows item=_row}}
    <tr data-axis_id="{{$_row->_id}}" {{if !$_row->active}}class="hatching opacity-60"{{/if}}>
      <td style="font-size: 1px;">
        {{if $_row->color}}
          <div
            style="display: inline-block; width: 6px; height: 14px; background-color: #{{$_row->color}}; vertical-align: middle; margin-right: 1px;"></div>
        {{/if}}
      </td>
      <td>
        <a href="#1" onclick="return SupervisionGraph.editTableRow({{$_row->_id}})">
          {{mb_value object=$_row field=title}}
        </a>
      </td>
      <td>{{mb_value object=$_row field=value_type_id}}</td>
      <td>{{mb_value object=$_row field=value_unit_id}}</td>
      <td>
        <button class="edit notext compact me-tertiary me-dark" onclick="SupervisionGraph.editTableRow({{$_row->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CSupervisionTableRow.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

<button class="new me-primary me-float-none me-margin-top-4" onclick="SupervisionGraph.editTableRow(0, {{$table->_id}})" style="float: right;">
  {{tr}}CSupervisionTableRow-title-create{{/tr}}
</button>