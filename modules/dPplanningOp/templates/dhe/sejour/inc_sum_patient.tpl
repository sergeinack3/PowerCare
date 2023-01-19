{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-ald" class="dhe_flag dhe_flag_info"
      title="{{tr}}CSejour-ald-desc{{/tr}}"{{if !$patient->ald}} style="display: none;"{{/if}}>
  ALD
</span>

<span id="sejour-c2s" class="dhe_flag dhe_flag_info"
      title="{{tr}}CPatient-c2s-desc{{/tr}}"{{if !$patient->c2s}} style="display: none;"{{/if}}>
  C2S
</span>

<span id="sejour-acs" class="dhe_flag dhe_flag_info"
      title="{{tr}}CPatient-acs-desc{{/tr}}{{if $patient->acs_type}} contrat {{mb_value object=$patient field=acs_type}}{{/if}}"{{if !$patient->acs}} style="display: none;"{{/if}}>
  ACS
</span>

<span id="sejour-tutelle" class="dhe_flag dhe_flag_info"
      title="{{if $patient->acs_type != 'aucune'}}{{mb_value object=$patient field=tutelle}}{{/if}}"{{if !$patient->tutelle || $patient->tutelle == 'aucune'}} style="display: none;"{{/if}}>
  Tutelle
</span>

{{if 'dPfacturation'|module_active && "dPplanningOp CSejour fields_display assurances"|gconf}}
    <span id="sejour-_dialyse" class="dhe_flag dhe_flag_info"
          title="{{tr}}CSejour-_dialyse-desc{{/tr}}"{{if !$sejour->_dialyse}} style="display: none;"{{/if}}>
    {{tr}}CSejour-_dialyse{{/tr}}
  </span>
    <span id="sejour-_cession_creance" class="dhe_flag dhe_flag_info"
          title="{{tr}}CSejour-_cession_creance-desc{{/tr}}"{{if !$sejour->_cession_creance}} style="display: none;"{{/if}}>
    {{tr}}CSejour-_cession_creance{{/tr}}
  </span>
    <span id="sejour-_type_sejour" class="dhe_sum_item"
          title="{{tr}}CSejour-_type_sejour-desc{{/tr}}"{{if !$sejour->_type_sejour}} style="display: none;"{{/if}}>
    {{mb_value object=$sejour field=_type_sejour}}
  </span>
    <span id="sejour-_statut_pro" class="dhe_sum_item"
          title="{{tr}}CSejour-_statut_pro-desc{{/tr}}"{{if !$sejour->_statut_pro}} style="display: none;"{{/if}}>
    {{mb_value object=$sejour field=_statut_pro}}
  </span>
    <span id="sejour-_assurance_maladie" class="dhe_sum_item" class="dhe_flag dhe_flag_info"
          title="{{if $sejour->_rques_assurance_maladie}}Remarques : {{$sejour->_rques_assurance_maladie}}{{else}}{{tr}}CSejour-_assurance_maladie-desc{{/tr}}{{/if}}"{{if !$sejour->_assurance_maladie}} style="display: none;"{{/if}}>
    {{if $sejour->_ref_facture}}
        {{$sejour->_ref_facture->_ref_assurance_maladie}}
    {{/if}}
  </span>
{{/if}}
