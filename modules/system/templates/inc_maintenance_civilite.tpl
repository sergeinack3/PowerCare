{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function display_civilites() {
    var url = new Url('system', 'vw_repair_civilite');
    url.requestModal("80%", "80%");
  }
</script>

<table class="main tbl">
  <tr>
    <th>{{tr}}mod-system-repair-civilite{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button class="lookup" type="button" onclick="display_civilites()">{{tr}}mod-system-repair-civilite-display{{/tr}}</button>
    </td>
  </tr>
</table>