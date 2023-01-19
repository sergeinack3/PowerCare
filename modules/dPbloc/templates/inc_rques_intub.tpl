{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour value=$operation->_ref_sejour}}
{{assign var=dossier_medical value=$sejour->_ref_patient->_ref_dossier_medical}}

{{if $operation->rques || ($consult_anesth && $consult_anesth->_intub_difficile) || ($dossier_medical && $dossier_medical->risque_MCJ_patient == 'possible')}}
  <div class="small-warning">
    {{mb_value object=$operation field=rques}}
    {{if $consult_anesth->_id && $consult_anesth->_intub_difficile}}
      <div style="font-weight: bold; color:#f00;">
        {{tr}}CConsultAnesth-_intub_difficile{{/tr}}
      </div>
    {{/if}}
    {{if $dossier_medical && $dossier_medical->risque_MCJ_patient == 'possible'}}
      <div>
        {{mb_label object=$dossier_medical field=risque_MCJ_patient}}: <span style="font-weight: bold;">{{mb_value object=$dossier_medical field=risque_MCJ_patient}}</span>
      </div>
    {{/if}}
  </div>
{{/if}}
{{if $sejour && $sejour->_id && $sejour->ATNC != ""}}
  <div style="font-weight: bold; {{if $sejour->ATNC == 1}}color: #f00;{{/if}}"
       class="{{if $sejour->ATNC == 1}}small-warning{{else}}small-info{{/if}}">
    {{if $sejour->ATNC}}Risque ATNC{{else}}Aucun risque ATNC{{/if}}
  </div>
{{/if}}