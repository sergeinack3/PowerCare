{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-materiel" class="dhe_sum_item" title="{{tr}}COperation-materiel{{/tr}}" {{if !$operation->materiel}}style="display: none;"{{/if}}>
  {{$operation->materiel|truncate:50}}
</span>