{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl me-margin-top-0 me-no-border-radius-top">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CSupervisionGraphAxis field=title}}</th>
    <th colspan="2">{{mb_title class=CSupervisionGraphAxis field=limit_low}}
      / {{mb_title class=CSupervisionGraphAxis field=limit_high}}</th>
    <th>{{mb_title class=CSupervisionGraphAxis field=display}}</th>
    <th>{{mb_title class=CSupervisionGraphAxis field=show_points}}</th>
    <th>{{mb_title class=CSupervisionGraphAxis field=symbol}}</th>
    <th class="narrow"></th>
  </tr>
  
  {{foreach from=$axes item=_axis}}
    <tr data-axis_id="{{$_axis->_id}}" {{if !$_axis->actif}}class="hatching opacity-60"{{/if}}>
      <td style="font-size: 1px;">
        {{foreach from=$_axis->_back.series item=_series}}
          <div
            style="display: inline-block; width: 6px; height: 14px; background-color: #{{$_series->color}}; vertical-align: middle; margin-right: 1px;"
            onmouseover="ObjectTooltip.createEx(this, '{{$_series->_guid}}');"></div>
        {{/foreach}}
      </td>
      <td>
        <a href="#1" onclick="return SupervisionGraph.editAxis({{$_axis->_id}})">
          {{mb_value object=$_axis field=title}}
        </a>
      </td>
      <td style="width: 3em;">{{mb_value object=$_axis field=limit_low}}</td>
      <td style="width: 3em;">{{mb_value object=$_axis field=limit_high}}</td>
      <td>{{mb_value object=$_axis field=display}}</td>
      <td>{{mb_value object=$_axis field=show_points}}</td>
      <td>
        {{mb_include module=patients template=inc_axis_symbol axis=$_axis}}
        {{mb_value object=$_axis field=symbol}}
      </td>
      <td>
        <button class="edit notext compact me-tertiary me-dark" onclick="SupervisionGraph.editAxis({{$_axis->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CSupervisionGraphAxis.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

{{if $counter_axes_active < 4}}
  <button class="new me-primary me-margin-top-4 me-float-none" onclick="SupervisionGraph.editAxis(0, {{$graph->_id}})" style="float: right;">
    {{tr}}CSupervisionGraphAxis-title-create{{/tr}}
  </button>
{{/if}}
