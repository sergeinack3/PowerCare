{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th style="width: 16px;"></th>
    <th>{{mb_title class=CSupervisionGraphSeries field=title}}</th>
    <th>{{mb_title class=CSupervisionGraphSeries field=value_type_id}}</th>
    <th>{{mb_title class=CSupervisionGraphSeries field=value_unit_id}}</th>
    <th>{{mb_title class=CSupervisionGraphSeries field=display_ratio_time}}</th>
    <th class="narrow"></th>
  </tr>
  
  {{foreach from=$series item=_series}}
    <tr>
      <td style="background-color: #{{$_series->color}}"></td>
      <td>
        <a href="#1" onclick="return SupervisionGraph.editSeries({{$_series->_id}})">
          {{$_series}}
        </a>
      </td>
      <td>{{mb_value object=$_series field=value_type_id}}</td>
      <td>{{mb_value object=$_series field=value_unit_id}}</td>
      <td>
        {{if $_series->display_ratio_value && $_series->display_ratio_time}}
          {{mb_value object=$_series field=display_ratio_value}}
          {{$_series->_ref_value_unit->label}}
          =
          {{mb_value object=$_series field=display_ratio_time}}
          minutes
        {{/if}}
      </td>
      <td>
        <button class="edit notext compact me-tertiary me-dark" onclick="SupervisionGraph.editSeries({{$_series->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CSupervisionGraphSeries.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

<button class="new me-primary" onclick="SupervisionGraph.editSeries(0, {{$axis->_id}})">
  {{tr}}CSupervisionGraphSeries-title-create{{/tr}}
</button>