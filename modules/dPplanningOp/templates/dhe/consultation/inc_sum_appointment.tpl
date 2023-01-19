{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="consult-chrono" class="dhe_sum_item" title="{{tr}}CConsultation-chrono-desc{{/tr}}"{{if !$consult->chrono}} style="display: none;"{{/if}}>
  {{mb_value object=$consult field=chrono}}
</span>

<span id="consult-categorie_id" class="dhe_sum_item" title="{{tr}}CConsultation-categorie_id-desc{{/tr}}"{{if !$consult->categorie_id}} style="display: none;"{{/if}}>
  {{if $consult->categorie_id}}
    {{$consult->_ref_categorie}}
  {{/if}}
</span>

<span id="consult-duree" class="dhe_sum_item" title="{{tr}}CConsultation-duree-desc{{/tr}}"{{if !$consult->duree}} style="display: none;"{{/if}}>
  {{$consult->_duree}} min
</span>

<span id="consult-grossesse_id" class="dhe_sum_item" title="{{tr}}CConsultation-grossesse_id-desc{{/tr}}" onmouseover="{{if $sejour->grossesse_id}}ObjectTooltip.createEx(this, 'CGrossesse{{$sejour->grossesse_id}}');{{/if}}"{{if !$consult->grossesse_id}} style="display: none;"{{/if}}>
  Grossesse
</span>

<span id="consult-adresse_par_prat_id" class="dhe_sum_item" title="{{tr}}CConsultation-adresse_par_prat_id-desc{{/tr}}"{{if !$consult->adresse_par_prat_id}} style="display: none;"{{/if}}>
  {{if $consult->adresse_par_prat_id}}
    {{$consult->_ref_adresse_par_prat}}
  {{/if}}
</span>

<span id="consult-visite_domicile" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-visite_domicile-desc{{/tr}}"{{if !$consult->visite_domicile}} style="display: none;"{{/if}}>
  Visite
</span>

<span id="consult-premiere" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-premiere-desc{{/tr}}"{{if !$consult->premiere}} style="display: none;"{{/if}}>
  1ère
</span>

<span id="consult-derniere" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-derniere-desc{{/tr}}"{{if !$consult->derniere}} style="display: none;"{{/if}}>
  Dernière
</span>

<span id="consult-si_desistement" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-si_desistement-desc{{/tr}}"{{if !$consult->si_desistement}} style="display: none;"{{/if}}>
  {{tr}}CConsultation-si_desistement-court{{/tr}}
</span>