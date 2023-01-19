{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-presence_confidentielle" class="dhe_flag dhe_flag_warning" title="{{tr}}CSejour-presence_confidentielle-desc{{/tr}}"{{if $sejour->presence_confidentielle != '1'}} style="display: none;"{{/if}}>
  ND
</span>

<span id="sejour-entree_reelle" class="dhe_sum_item" title="{{tr}}CSejour-entree_reelle-desc{{/tr}}"{{if !$sejour->entree_reelle || $sejour->entree_prevue}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=entree_reelle}}
</span>

<span id="sejour-entree_prevue" class="dhe_sum_item" title="{{tr}}CSejour-entree_prevue-desc{{/tr}}"{{if !$sejour->entree_prevue}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=entree_prevue}}
</span>

{{if $conf.dPplanningOp.CSejour.use_custom_mode_entree}}
  <span id="sejour-mode_entree_id" class="dhe_sum_item" title="{{tr}}CSejour-mode_entree_id-desc{{/tr}}"{{if !$sejour->mode_entree_id}} style="display: none;"{{/if}}>
    {{$sejour->_ref_mode_entree}}
  </span>
{{/if}}

<span id="sejour-mode_entree" class="dhe_sum_item" title="{{tr}}CSejour-mode_entree-desc{{/tr}}"{{if !$sejour->mode_entree}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=mode_entree}}
</span>

<span id="sejour-etablissement_entree_id" class="dhe_sum_item" title="{{tr}}CSejour-etablissement_entree_id-desc{{/tr}}"{{if !$sejour->etablissement_entree_id}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour->_ref_etablissement_provenance field=nom}}
</span>

<span id="sejour-provenance" class="dhe_sum_item" title="{{tr}}CSejour-provenance-desc{{/tr}}"{{if !$sejour->provenance}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=provenance}}
</span>

<span id="sejour-date_entree_reelle_provenance" class="dhe_sum_item" title="{{tr}}CSejour-date_entree_reelle_provenance-desc{{/tr}}"{{if !$sejour->date_entree_reelle_provenance}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=date_entree_reelle_provenance}}
</span>

<span id="sejour-service_entree_id" class="dhe_sum_item" title="{{tr}}CSejour-service_entree_id-desc{{/tr}}"{{if !$sejour->service_entree_id}} style="display: none;"{{/if}}>
  {{$sejour->_ref_service_provenance}}
</span>

<span id="sejour-adresse_par_prat_id" class="dhe_sum_item" title="{{tr}}CSejour-adresse_par_prat_id-desc{{/tr}}"{{if !$sejour->adresse_par_prat_id}} style="display: none;"{{/if}}>
  {{$sejour->_ref_adresse_par_prat}}
</span>

