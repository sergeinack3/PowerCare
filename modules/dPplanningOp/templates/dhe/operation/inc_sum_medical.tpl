{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-exam_extempo" class="dhe_flag dhe_flag_extempo" {{if !$operation->exam_extempo}}style="display: none;"{{/if}}
      title="{{tr}}COperation-exam_extempo{{/tr}}">
  EXTEMPO
</span>

<span id="operation-info" class="dhe_flag dhe_flag_info" {{if !$operation->info}}style="display: none;"{{/if}}
      title="{{tr}}COperation-info{{/tr}}">
  INFO
</span>

<span id="operation-rques" class="dhe_sum_item" {{if !$operation->rques}}style="display: none;"{{/if}}>
  Rem: {{$operation->rques|truncate:50}}
</span>