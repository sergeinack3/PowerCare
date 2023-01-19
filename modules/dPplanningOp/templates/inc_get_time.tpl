{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $javascript}}
<script type="text/javascript">
periodicalTimeUpdater.currentlyExecuting = true;
</script>
{{/if}}

<em style="padding: 2px 5px">{{tr}}COperation-msg-EstimateTime{{/tr}} : {{$temps|default:'-'}}</em>
