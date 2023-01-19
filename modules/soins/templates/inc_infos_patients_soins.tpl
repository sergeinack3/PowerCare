{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=add_class    value=0}}
{{mb_default var=with_buttons value=1}}

{{assign var=constantes value=$patient->_ref_constantes_medicales}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{math assign=pct_width equation="100/8" format="%.2f"}}

<tr id="infos_patient_soins">
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    {{mb_include module=soins template=inc_infos_patients_soins_constante constantes=$constantes
    constante="poids" add_class=$add_class}}
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    {{mb_include module=soins template=inc_infos_patients_soins_constante constantes=$constantes
    constante="taille" add_class=$add_class}}
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    {{mb_include module=soins template=inc_infos_patients_soins_constante constantes=$constantes
    constante="_imc" add_class=$add_class}}
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    {{mb_include module=soins template=inc_infos_patients_soins_constante constantes=$constantes
    constante="_surface_corporelle" add_class=$add_class}}
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    {{if $constantes->creatininemie}}
      {{mb_include module=soins template=inc_infos_patients_soins_constante constante="creatininemie" add_class=$add_class}}
    {{elseif $constantes->mdrd}}
      {{mb_include module=soins template=inc_infos_patients_soins_constante constante="mdrd" add_class=$add_class}}
    {{elseif $constantes->clair_creatinine}}
      {{mb_include module=soins template=inc_infos_patients_soins_constante constante="clair_creatinine" add_class=$add_class}}
    {{/if}}
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    <strong>{{mb_title object=$patient field=naissance}}:</strong>
    <span>{{mb_value object=$patient field=naissance}} ({{$patient->_age}})</span>
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    <strong>{{mb_title object=$patient field=sexe}}:</strong>
    <span>{{mb_value object=$patient field=sexe}}</span>
  </td>
  <td class="me-patient-banner-info-patient" style="width: {{$pct_width}}%;">
    <strong>{{mb_title object=$dossier_medical field=groupe_sanguin}}:</strong>
    {{if in_array($dossier_medical->groupe_sanguin, array("O", "A", "B", "AB"))}}
      <span>
          {{mb_value object=$dossier_medical field=groupe_sanguin}}
          {{if $dossier_medical->rhesus == "POS"}}+{{elseif $dossier_medical->rhesus == "NEG"}}-{{/if}}
      </span>
    {{else}}
      <span>
        &mdash;
      </span>
    {{/if}}

    {{if $with_buttons}}
      <button class="{{if $app->_ref_user->isPraticien()}}edit{{else}}search{{/if}} notext me-color-white"
              style="background-color: white" type="button" onclick="Patient.editGroupeSanguin('{{$patient->_id}}')">
      </button>
    {{/if}}
  </td>
</tr>
