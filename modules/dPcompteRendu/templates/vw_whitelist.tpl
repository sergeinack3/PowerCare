{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=whitelist ajax=true}}

<script>
  Main.add(WhiteList.refreshList);
</script>

<div class="me-no-align">
  <button type="button" class="new" onclick="WhiteList.edit();">{{tr}}CWhiteList.new{{/tr}}</button>
</div>

<div class="small-info">
  {{tr}}CWhiteList-Usage{{/tr}}
</div>

<div id="whitelist_area" class="me-no-align"></div>