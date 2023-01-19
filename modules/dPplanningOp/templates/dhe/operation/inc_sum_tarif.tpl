{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-conventionne" class="dhe_flag dhe_flag_conventionne" title="{{tr}}COperation-conventionne{{/tr}}" {{if !$operation->conventionne}}style="display: none;"{{/if}}>
  CONV.
</span>

<span id="operation-depassement" class="dhe_sum_item" title="{{tr}}COperation-depassement{{/tr}}" {{if !$operation->depassement}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=depassement}}
</span>

<span id="operation-forfait" class="dhe_sum_item" title="{{tr}}COperation-forfait{{/tr}}" {{if !$operation->forfait}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=forfait}}
</span>

<span id="operation-fournitures" class="dhe_sum_item" title="{{tr}}COperation-fournitures{{/tr}}" {{if !$operation->fournitures}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=fournitures}}
</span>