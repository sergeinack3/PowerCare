{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=route ajax=1}}

<script>
  Main.add(
    function () {
      Route.refreshList();
    }
  )
</script>

<button type="button" class="new" onclick="Route.edit(null, Route.refreshList)">
  {{tr}}CEAIRoute-new{{/tr}}
</button>

<div id="list_route"></div>