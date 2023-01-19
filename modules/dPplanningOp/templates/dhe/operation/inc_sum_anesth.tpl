{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-ASA" class="dhe_flag dhe_anesth_ASA" title="{{tr}}COperation-ASA{{/tr}}" {{if !$operation->ASA}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=ASA}}
</span>

<span id="operation-anesth_id" class="dhe_sum_item" title="{{tr}}COperation-anesth_id{{/tr}}" {{if !$operation->anesth_id}}style="display: none"{{/if}}>
  {{$operation->_ref_anesth}}
</span>

<span id="operation-type_anesth" class="dhe_sum_item" title="{{tr}}COperation-type_anesth{{/tr}}" {{if !$operation->type_anesth}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=type_anesth}}
</span>

<span id="operation-position" class="dhe_sum_item" title="{{tr}}COperation-position_id{{/tr}}" {{if !$operation->position_id}}style="display: none"{{/if}}>
  {{mb_value object=$operation field=position_id}}
</span>