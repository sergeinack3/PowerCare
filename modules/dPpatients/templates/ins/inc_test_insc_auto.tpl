{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2" class="title">
      {{tr}}Total{{/tr}} : {{$result.total}}
    </th>
  </tr>
  <tr>
    <th>
      {{tr}}OK{{/tr}}
    </th>
    <th>
      {{tr}}Error{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      {{$result.correct}}
    </td>
    <td>
      {{$result.incorrect}}
    </td>
  </tr>
</table>
