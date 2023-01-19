{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="narrow">{{mb_title class=CSupervisionGraphAxisValueLabel field=value}}</th>
    <th>{{mb_title class=CSupervisionGraphAxisValueLabel field=title}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$labels item=_label}}
    <tr>
      <td>
        <a href="#1" onclick="return SupervisionGraph.editAxisLabel({{$_label->_id}})">
          {{mb_value object=$_label field=value}}
        </a>
      </td>
      <td>
        <a href="#1" onclick="return SupervisionGraph.editAxisLabel({{$_label->_id}})">
          {{mb_value object=$_label field=title}}
        </a>
      </td>
      <td>
        <button class="edit notext compact me-tertiary me-dark" onclick="SupervisionGraph.editAxisLabel({{$_label->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">
        {{tr}}CSupervisionGraphAxisValueLabel.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

<button class="new me-primary" onclick="SupervisionGraph.editAxisLabel(0, {{$axis->_id}})">
  {{tr}}CSupervisionGraphAxisValueLabel-title-create{{/tr}}
</button>