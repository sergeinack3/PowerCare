{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showIncrementer = function (incrementer_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("dPsante400", "ajax_edit_incrementer")
      .addParam("incrementer_id", incrementer_id)
      .requestUpdate("vw_incrementer");
  }
</script>

<table class="main">
  <tr>
    <td style="width: 60%">
      <a href="#" onclick="showIncrementer(0)" class="button new">
        {{tr}}CIncrementer-title-create{{/tr}}
      </a>
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include template=inc_list_incrementers}}
    </td>
    <td style="width: 40%" id="vw_incrementer">
      {{mb_include template=inc_edit_incrementer}}
    </td>
  </tr>
</table>