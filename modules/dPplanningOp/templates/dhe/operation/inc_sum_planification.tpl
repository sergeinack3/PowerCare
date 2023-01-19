{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-urgence" class="dhe_flag dhe_flag_urgence" {{if !$operation->urgence}}style="display: none;"{{/if}}>
  URG
</span>

<span id="operation-date" class="dhe_sum_item" title="{{tr}}COperation-date{{/tr}}" {{if !$operation->_id}}style="display: none;"{{/if}}>
  {{$operation->_datetime|date_format:$conf.date}}
</span>

<span id="operation-_time_urgence" class="dhe_sum_item" title="{{tr}}COperation-time_operation{{/tr}}" {{if !$operation->_id}}style="display: none;"{{/if}}>
  {{$operation->_datetime|date_format:$conf.time}}
</span>

<span id="operation-salle_id" class="dhe_sum_item" title="{{tr}}COperation-salle_id{{/tr}}">
  {{$operation->_ref_salle}}
</span>

<span id="operation-cote" class="dhe_sum_item" title="{{tr}}COperation-cote{{/tr}}" {{if !$operation->cote}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=cote}}
</span>