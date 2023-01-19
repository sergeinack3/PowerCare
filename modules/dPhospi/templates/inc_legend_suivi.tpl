{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-box-shadow me-margin-bottom-4">
  <tr>
    <th>Type</th>
    <th>Degré</th>
  </tr>
  <tr class="consultation_entree">
    <td colspan="2">
      Observation d'entrée
    </td>
  </tr>
  <tr class="observation_urgente">
    <td>
      Observation
    </td>
    <td>
      Urgent
    </td>
  </tr>
  <tr class="observation_info">
    <td>
      Observation
    </td>
    <td>
      Info
    </td>
  </tr>
  <tr class="transmission_haute">
    <td>
      Transmission
    </td>
    <td>
      Haut
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>