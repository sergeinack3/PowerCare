{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="operation-_time_op" class="dhe_sum_item" title="{{tr}}COperation-_time_op{{/tr}}" {{if !$operation->_time_op}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=_time_op}}
</span>

<span id="operation-duree_preop" class="dhe_sum_item" title="{{tr}}COperation-duree_preop{{/tr}}" {{if !$operation->duree_preop}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=duree_preop}}
</span>

<span id="operation-presence_preop" class="dhe_sum_item" title="{{tr}}COperation-presence_preop{{/tr}}" {{if !$operation->presence_preop}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=presence_preop}}
</span>

<span id="operation-presence_postop" class="dhe_sum_item" title="{{tr}}COperation-presence_postop{{/tr}}" {{if !$operation->presence_postop}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=presence_postop}}
</span>

<span id="operation-duree_uscpo" class="dhe_sum_item" title="{{tr}}COperation-duree_uscpo{{/tr}}" {{if !$operation->duree_uscpo}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=duree_uscpo}}
</span>

<span id="operation-duree_bio_nettoyage" class="dhe_sum_item" title="{{tr}}COperation-duree_bio_nettoyage{{/tr}}" {{if !$operation->duree_bio_nettoyage}}style="display: none;"{{/if}}>
  {{mb_value object=$operation field=duree_bio_nettoyage}}
</span>