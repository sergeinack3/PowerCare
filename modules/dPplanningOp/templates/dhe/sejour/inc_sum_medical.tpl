{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-ATNC" class="dhe_flag dhe_flag_important" title="{{tr}}CSejour-ATNC-desc{{/tr}}"{{if $sejour->ATNC != '1'}} style="display: none;"{{/if}}>
  ATNC
</span>

<span id="sejour-isolement" class="dhe_flag dhe_flag_important" title="Du {{mb_value object=$sejour field=isolement_date}} au {{mb_value object=$sejour field=isolement_fin}}"{{if $sejour->isolement != '1'}} style="display: none;"{{/if}}>
  ISOL
</span>

<span id="sejour-handicap" class="dhe_flag dhe_flag_important" title="
    {{foreach from=$patient->_refs_patient_handicaps item=_handicap}}
        {{tr}}CPatientHandicap.handicap.{{$_handicap->handicap}}{{/tr}}.
    {{/foreach}}"
    {{if !$patient->_refs_patient_handicaps}} style="display: none;"{{/if}}>
  HDCP
</span>

<span id="sejour-reanimation" class="dhe_flag dhe_flag_important" title="{{tr}}CSejour-reanimation-desc{{/tr}}"{{if $sejour->reanimation != '1'}} style="display: none;"{{/if}}>
  REA
</span>

<span id="sejour-UHCD" class="dhe_flag dhe_flag_important" title="{{tr}}CSejour-UHCD-desc{{/tr}}"{{if $sejour->UHCD != '1'}} style="display: none;"{{/if}}>
  UHCD
</span>

<span id="sejour-consult_accomp" class="dhe_flag dhe_flag_info" title="{{tr}}CSejour-consult_accomp-desc{{/tr}}"{{if $sejour->consult_accomp != '1'}} style="display: none;"{{/if}}>
  Consult. acc.
</span>

<span id="sejour-date_accident" class="dhe_flag dhe_flag_warning" title="{{if $sejour->date_accident}}Date: {{mb_value object=$sejour field=date_accident}}, {{/if}}{{if $sejour->nature_accident}}Nature : {{mb_value object=$sejour field=nature_accident}}{{/if}}"{{if !$sejour->date_accident}} style="display: none;"{{/if}}>
  Accident
</span>

<span id="sejour-grossesse_id" class="dhe_sum_item" onmouseover="{{if $sejour->grossesse_id}}ObjectTooltip.createEx(this, 'CGrossesse{{$sejour->grossesse_id}}');{{/if}}"{{if !$sejour->grossesse_id}} style="display: none;"{{/if}}>
  Grossesse
</span>

<span id="sejour-rques" class="dhe_sum_item" title="{{tr}}CSejour-rques{{/tr}} : {{$sejour->rques}}" class="text"{{if !$sejour->rques}} style="display: none;"{{/if}}>
  Rem: {{$sejour->rques|truncate:50}}
</span>

<span id="sejour-convalescence" class="dhe_sum_item" title="{{tr}}CSejour-convalescence{{/tr}} : {{$sejour->convalescence}}" class="text"{{if !$sejour->convalescence}} style="display: none;"{{/if}}>
  Conv: {{$sejour->convalescence|truncate:50}}
</span>
