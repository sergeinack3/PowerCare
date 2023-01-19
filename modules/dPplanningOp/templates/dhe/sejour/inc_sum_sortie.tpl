{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-sortie_reelle" class="dhe_sum_item" title="{{tr}}CSejour-sortie_reelle-desc{{/tr}}"{{if !$sejour->sortie_reelle}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=sortie_reelle}}
</span>

<span id="sejour-sortie_prevue" class="dhe_sum_item" title="{{tr}}CSejour-sortie_prevue-desc{{/tr}}"{{if !$sejour->sortie_prevue}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=sortie_prevue}}
</span>

<span id="sejour-mode_sortie_id" class="dhe_sum_item" title="{{tr}}CSejour-mode_sortie_id-desc{{/tr}}"{{if !$sejour->mode_sortie_id}} style="display: none;"{{/if}}>
  {{$sejour->_ref_mode_sortie}}
</span>

<span id="sejour-mode_sortie" class="dhe_sum_item" title="{{tr}}CSejour-mode_sortie-desc{{/tr}}"{{if !$sejour->mode_sortie}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=mode_sortie}}
</span>

<span id="sejour-transport_sortie" class="dhe_sum_item" title="{{tr}}CSejour-transport_sortie-desc{{/tr}}"{{if !$sejour->transport_sortie}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=transport_sortie}}
</span>

<span id="sejour-etablissement_sortie_id" class="dhe_sum_item" title="{{tr}}CSejour-etablissement_sortie_id-desc{{/tr}}"{{if !$sejour->etablissement_sortie_id}} style="display: none;"{{/if}}>
  {{$sejour->_ref_etablissement_transfert}}
</span>

<span id="sejour-service_sortie_id" class="dhe_sum_item" title="{{tr}}CSejour-service_sortie_id-desc{{/tr}}"{{if !$sejour->service_sortie_id}} style="display: none;"{{/if}}>
  {{$sejour->_ref_service_mutation}}
</span>

<span id="sejour-_date_deces" class="dhe_sum_item" title="{{tr}}CSejour-_date_deces-desc{{/tr}}"{{if !$sejour->_date_deces}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=_date_deces}}
</span>

<span id="sejour-destination" class="dhe_sum_item" title="{{tr}}CSejour-destination-desc{{/tr}}"{{if !$sejour->destination}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=destination}}
</span>