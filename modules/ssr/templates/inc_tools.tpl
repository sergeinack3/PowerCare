{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  modalActesDoublons = function() {
    new Url("ssr", "vw_doublons_actes")
      .requestModal("80%", "80%");
  };
  modalEvtsNoRealise = function() {
    new Url("ssr", "vw_correct_evt_completed")
      .requestModal("80%", "80%");
  };
</script>

<table class="tbl">
  <tr>
    <td>
      <button type="button" class="search" onclick="modalActesDoublons();">{{tr}}ssr-tools-correct_doublons{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <td>
      <button type="button" class="search" onclick="modalEvtsNoRealise();">
        {{tr}}ssr-tools-correct_seance_collectives{{/tr}}
      </button>
    </td>
  </tr>
</table>