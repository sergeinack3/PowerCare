{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=ampli ajax=$ajax}}

<script>
  Main.add(function() {
    Ampli.refreshList();
  });
</script>

<div>
  <button class="new" onclick="Ampli.edit();">{{tr}}CAmpli-title-create{{/tr}}</button>
</div>

<div id="amplis_area"></div>
