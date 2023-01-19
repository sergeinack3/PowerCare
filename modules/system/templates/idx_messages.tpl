{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=message}}

<script type="text/javascript">
Main.add(Message.refreshList);
</script>

<button class="new singleclick" onclick="Message.edit(0);">
  {{tr}}CMessage-title-create{{/tr}}
</button>

<button class="new singleclick" onclick="Message.createUpdate();" style="float:right;">
  {{tr}}CMessage-title-create_update{{/tr}}
</button>

<div id="list-messages"></div>
