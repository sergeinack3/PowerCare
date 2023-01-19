{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="consult-secteur1" class="dhe_sum_item" title="{{tr}}CConsultation-secteur1-desc{{/tr}}"{{if !$consult->secteur1}} style="display: none;"{{/if}}>
  {{mb_value object=$consult field=secteur1}}
</span>

<span id="consult-secteur2" class="dhe_sum_item" title="{{tr}}CConsultation-secteur2-desc{{/tr}}"{{if !$consult->secteur2}} style="display: none;"{{/if}}>
  DH: {{mb_value object=$consult field=secteur2}}
</span>

<span id="consult-secteur3" class="dhe_sum_item" title="{{tr}}CConsultation-secteur3-desc{{/tr}}"{{if !$consult->secteur3}} style="display: none;"{{/if}}>
  TVA: {{mb_value object=$consult field=secteur3}}
</span>

<span id="consult-patient_date_reglement" class="dhe_sum_item" title="{{tr}}CFactureCabinet-patient_date_reglement-desc{{/tr}}"
  {{if !$consult->_ref_facture->patient_date_reglement}} style="display: none;"{{/if}}>
  {{mb_value object=$consult->_ref_facture field=patient_date_reglement}}
</span>

<span id="consult-tiers_date_reglement" class="dhe_sum_item" title="{{tr}}CFactureCabinet-tiers_date_reglement-desc{{/tr}}"
  {{if !$consult->_ref_facture->tiers_date_reglement}} style="display: none;"{{/if}}>
  {{mb_value object=$consult->_ref_facture field=tiers_date_reglement}}
</span>

<span id="consult-du_patient" class="dhe_sum_item" title="{{tr}}CConsultation-du_patient-desc{{/tr}}"{{if !$consult->du_patient}} style="display: none;"{{/if}}>
  Dû patient: {{mb_value object=$consult field=du_patient}}
</span>

<span id="consult-du_tiers" class="dhe_sum_item" title="{{tr}}CConsultation-du_tiers-desc{{/tr}}"{{if !$consult->du_tiers}} style="display: none;"{{/if}}>
 Dû tiers: {{mb_value object=$consult field=du_tiers}}
</span>

<span id="consult-concerne_ALD" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-concerne_ALD-desc{{/tr}}"{{if !$consult->concerne_ALD}} style="display: none;"{{/if}}>
  ALD
</span>

<span id="consult-valide" class="dhe_flag dhe_flag_info" title="{{tr}}CConsultation-valide-desc{{/tr}}"{{if !$consult->valide}} style="display: none;"{{/if}}>
  Clôturée
</span>