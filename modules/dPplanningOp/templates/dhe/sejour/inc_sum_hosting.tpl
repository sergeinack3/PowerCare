{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sejour->_ref_last_affectation && $sejour->_ref_last_affectation->_id}}
  <span id="sejour-service_id" class="dhe_sum_item" title="Affectation en cours: {{$sejour->_ref_last_affectation}} &mdash; Du {{mb_value object=$sejour->_ref_last_affectation field=entree}} au {{mb_value object=$sejour->_ref_last_affectation field=sortie}}">
    {{$sejour->_ref_last_affectation}}
  </span>
{{else}}
  <span id="sejour-service_id" class="dhe_sum_item" title="{{tr}}CSejour-service_id-desc{{/tr}}"{{if !$sejour->service_id}} style="display: none;"{{/if}}>
    {{if $sejour->service_id}}
      {{$sejour->_ref_service}}
    {{/if}}
  </span>
{{/if}}

<span id="sejour-uf_hebergement_id" class="dhe_sum_item" title="{{tr}}CSejour-uf_hebergement_id{{/tr}}"{{if !$sejour->uf_hebergement_id}}style="display: none;"{{/if}}>
  {{if $sejour->uf_hebergement_id}}
    {{$sejour->_ref_uf_hebergement}}
  {{/if}}
</span>

<span id="sejour-uf_medicale_id" class="dhe_sum_item" title="{{tr}}CSejour-uf_medicale_id{{/tr}}"{{if !$sejour->uf_medicale_id}}style="display: none;"{{/if}}>
  {{if $sejour->uf_medicale_id}}
    {{$sejour->_ref_uf_medicale}}
  {{/if}}
</span>

<span id="sejour-uf_soins_id" class="dhe_sum_item" title="{{tr}}CSejour-uf_soins_id{{/tr}}"{{if !$sejour->uf_soins_id}}style="display: none;"{{/if}}>
  {{if $sejour->uf_soins_id}}
    {{$sejour->_ref_uf_soins}}
  {{/if}}
</span>

<span id="sejour-_unique_lit_id" class="dhe_sum_item" title="{{tr}}CSejour-_unique_lit_id-desc{{/tr}}" style="display: none;"></span>