{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=categories ajax=$ajax}}

<script>
  Main.add(function () {
    Categories.newCategory();
  });
</script>

<button type="button" id="new_category" class="button new">{{tr}}new-category{{/tr}}</button>

<div id="listCategories">{{mb_include module=astreintes template=inc_list_categories}}</div>
