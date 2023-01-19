{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=maintenanceConfig}}

<h2>{{tr}}CSourceIdentite-Actions{{/tr}}</h2>

<table class="tbl">
  <tr>
    <th class="section" style="width: 50%">{{tr}}Action{{/tr}}</th>
    <th class="section">{{tr}}Status{{/tr}}</th>
  </tr>

  <tr>
    <td>
      <button class="change" onclick="MaintenanceConfig.correctSources();">{{tr}}CSourceIdentite-Correct sources{{/tr}}</button>
    </td>
    <td id="source_correction_area"></td>
  </tr>
</table>
