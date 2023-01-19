{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-forfait_se" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-forfait_se-desc{{/tr}}"{{if !$sejour->forfait_se}} style="display: none;"{{/if}}>
  Forfait SE
</span>

<span id="sejour-forfait_sd" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-forfait_sd-desc{{/tr}}"{{if !$sejour->forfait_sd}} style="display: none;"{{/if}}>
  Forfait SD
</span>

<span id="sejour-hospit_de_jour" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-hospit_de_jour-desc{{/tr}}"{{if !$sejour->hospit_de_jour}} style="display: none;"{{/if}}>
  {{tr}}CSejour-hospit_de_jour{{/tr}}
</span>

<span id="sejour-type" class="dhe_sum_item" title="{{tr}}CSejour-type-desc{{/tr}}" {{if !$sejour->type}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=type}}
</span>

<span id="sejour-type_pec" class="dhe_sum_item" title="{{tr}}CSejour-type_pec-desc{{/tr}}"{{if !$sejour->type_pec}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=type_pec}}
</span>

<span id="sejour-modalite" class="dhe_sum_item" title="{{tr}}CSejour-modalite-desc{{/tr}}"{{if !$sejour->modalite}} style="display: none;"{{/if}}>
  {{mb_value object=$sejour field=modalite}}
</span>

<span id="sejour-charge_id" class="dhe_sum_item" title="{{tr}}CSejour-charge_id-desc{{/tr}}"{{if !$sejour->charge_id}} style="display: none;"{{/if}}>
  {{if $sejour->charge_id}}{{$sejour->_ref_charge_price_indicator}}{{/if}}
</span>

<span id="sejour-discipline_id" class="dhe_sum_item" title="{{tr}}CSejour-discipline_id-desc{{/tr}}: {{if $sejour->discipline_id}}{{$sejour->_ref_discipline_tarifaire->description}}{{else}}" style="display: none;{{/if}}">
{{if $sejour->discipline_id}}{{$sejour->_ref_discipline_tarifaire->nodess}}{{/if}}
</span>