{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-examen" class="dhe_sum_item" {{if !$operation->examen}}style="display: none;"{{/if}}>
  Bilan: {{$operation->examen|truncate:50}}
</span>

<span id="operation-exam_per_op" class="dhe_sum_item" {{if !$operation->exam_per_op}}style="display: none;"{{/if}}>
  Examen: {{$operation->exam_per_op|truncate:50}}
</span>