{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=categorie_rpu ajax=1}}

<script>
  Main.add(CategorieRPU.refreshList);
</script>

<button type="button" class="new" onclick="CategorieRPU.edit()">{{tr}}CRPUCategorie.new{{/tr}}</button>

<div id="categories_rpu"></div>