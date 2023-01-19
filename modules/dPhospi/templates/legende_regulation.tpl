{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">{{tr}}common-Icon{{/tr}}</th>
    <th class="title">{{tr}}Description{{/tr}}</th>
  </tr>
  <tr>
    <th colspan="2">{{tr}}CPatient-Patient status|pl{{/tr}}</th>
  </tr>
  <tr>
    <td style="text-align: right; font-weight: bold;" class="septique">M. X y</td>
    <td class="text">{{tr}}dPhospi-legend-patient-sceptic{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right; font-weight: bold;" class="patient-not-arrived">M. X y</td>
    <td class="text">{{tr}}dPhospi-legend-Patient to arrive in this room{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><strong>M. X y</strong></td>
    <td class="text">{{tr}}dPhospi-legend-patient-present{{/tr}}</td>
  </tr>
  <tr>
    <td style="background-image:url(images/icons/ray.gif); background-repeat:repeat; text-align: right; font-weight: bold;">M. X y</td>
    <td class="text">{{tr}}CPatient-Patient with medical release permission{{/tr}}</td>
  </tr>

  {{mb_include module=hospi template=inc_legend_bmr_bhre}}

  <tr>
    <td colspan="2" class="button">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>