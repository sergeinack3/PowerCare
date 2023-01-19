{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="consult-motif" class="dhe_sum_item" title="{{tr}}CConsultation-motif-desc{{/tr}}"{{if !$consult->motif}} style="display: none;"{{/if}}>
  Motif: {{$consult->motif|truncate:50}}
</span>

<span id="consult-rques" class="dhe_sum_item" title="{{tr}}CConsultation-rques-desc{{/tr}}"{{if !$consult->rques}} style="display: none;"{{/if}}>
  Rem: {{$consult->rques|truncate:50}}
</span>

<span id="consult-traitement" class="dhe_sum_item" title="{{tr}}CConsultation-traitement-desc{{/tr}}"{{if !$consult->traitement}} style="display: none;"{{/if}}>
  Trait: {{$consult->traitement|truncate:50}}
</span>

<span id="consult-histoire_maladie" class="dhe_sum_item" title="{{tr}}CConsultation-histoire_maladie-desc{{/tr}}"{{if !$consult->histoire_maladie}} style="display: none;"{{/if}}>
  Hist: {{$consult->histoire_maladie|truncate:50}}
</span>