{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=protocole_rpu ajax=1}}

<script>
  Main.add(ProtocoleRPU.refreshList);
</script>

<button type="button" class="new" onclick="ProtocoleRPU.edit()">{{tr}}CProtocoleRPU.new{{/tr}}</button>

<div id="protocoles_rpu"></div>