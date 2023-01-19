{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_medical_patient value=$patient->_ref_dossier_medical}}
{{assign var=dossier_medical_sejour value=$sejour->_ref_dossier_medical}}

<script>
  slowRisks = function() {
    $V(getForm('editThromboPatient').risque_thrombo_patient, 'faible');
    $V(getForm('editMCJPatient').risque_MCJ_patient, 'aucun');

    {{if $sejour->_id}}
      $V(getForm('editThromboChir').risque_thrombo_chirurgie, 'faible');
      $V(getForm('editMCJChir').risque_MCJ_chirurgie, 'sans');
      $V(getForm('editAntibioSejour').risque_antibioprophylaxie, 'non');
      $V(getForm('editProphylaxieSejour').risque_prophylaxie, 'non');
    {{/if}}
  };

  razRisks = function() {
    $V(getForm('editThromboPatient').risque_thrombo_patient, 'NR');
    $V(getForm('editMCJPatient').risque_MCJ_patient, 'NR');
    $V(getForm('editFacteursRisque').facteurs_risque, '');

    {{if $sejour->_id}}
      $V(getForm('editThromboChir').risque_thrombo_chirurgie, 'NR');
      $V(getForm('editMCJChir').risque_MCJ_chirurgie, 'NR');
      $V(getForm('editAntibioSejour').risque_antibioprophylaxie, 'NR');
      $V(getForm('editProphylaxieSejour').risque_prophylaxie, 'NR');
      $V(getForm('editPriseEnChargeAmbuSejour').pec_ambu, 'NR');
      $V(getForm('editRquesPriseEnChargeAmbuSejour').rques_pec_ambu, '');
    {{/if}}
  };
</script>


<table class="form me-margin-top-0 me-no-border-radius-top">
  <tr>
    <th class="category" style="width: 40%;" colspan="2">Facteur de risque</th>
    <th class="category">Patient</th>
    <th class="category">Chirurgie</th>
  </tr>

  <tr>
    <td rowspan="6" style="vertical-align: middle; text-align: center">
      <p class="not-printable"><button type="button" class="tick" onclick="slowRisks();"> Sans facteur de risque particulier</button></p>
      <p class="not-printable"><button type="button" class="undo me-tertiary" onclick="razRisks()">Réinitialiser</button></p>
    </td>
    <th>Maladie thromboembolique</th>
    <td style="text-align: center;">
      <form name="editThromboPatient" method="post" action="?">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$patient->_id}}" />
        <input type="hidden" name="object_class" value="CPatient" />
        {{mb_field object=$dossier_medical_patient field="risque_thrombo_patient" onchange="onSubmitFormAjax(this.form);"}}
      </form>
    </td>

    {{if $sejour->_id}}
      <td style="text-align: center;">
        <form name="editThromboChir" method="post" action="?">
          <input type="hidden" name="m" value="patients" />
          <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
          <input type="hidden" name="object_class" value="CSejour" />
          {{mb_field object=$dossier_medical_sejour field="risque_thrombo_chirurgie" onchange="onSubmitFormAjax(this.form);"}}
        </form>
      </td>
    {{else}}
    <td rowspan="6">
      <div class="small-info">
        Aucun séjour n'est associé à cette consultation
      </div>
    </td>
    {{/if}}
  </tr>

  <tr>
    <th>Prion (Maladie de Creutzfeldt-Jakob)</th>
    <td style="text-align: center;">
      <form name="editMCJPatient" method="post" action="?">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$patient->_id}}" />
        <input type="hidden" name="object_class" value="CPatient" />
        <select name="risque_MCJ_patient" onchange="onSubmitFormAjax(this.form);">
          <option value="NR"{{if $dossier_medical_patient->risque_MCJ_patient == 'NR'}} selected{{/if}}>{{tr}}CDossierMedical.risque_MCJ_patient.NR{{/tr}}</option>
          <option value="aucun"{{if $dossier_medical_patient->risque_MCJ_patient == 'aucun'}} selected{{/if}}>{{tr}}CDossierMedical.risque_MCJ_patient.aucun{{/tr}}</option>
          <option value="possible"{{if $dossier_medical_patient->risque_MCJ_patient == 'possible'}} selected{{/if}}>{{tr}}CDossierMedical.risque_MCJ_patient.possible{{/tr}}</option>
          {{if $dossier_medical_patient->risque_MCJ_patient != 'NR' && $dossier_medical_patient->risque_MCJ_patient != 'possible' && $dossier_medical_patient->risque_MCJ_patient != 'aucun'}}
            <option value="{{$dossier_medical_patient->risque_MCJ_patient}}" selected disabled>{{tr}}CDossierMedical.risque_MCJ_patient.{{$dossier_medical_patient->risque_MCJ_patient}}{{/tr}}</option>
          {{/if}}
        </select>
      </form>
    </td>
    {{if $sejour->_id}}
    <td style="text-align: center;">
      <form name="editMCJChir" method="post" action="?">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="object_class" value="CSejour" />
        {{mb_field object=$dossier_medical_sejour field="risque_MCJ_chirurgie" onchange="onSubmitFormAjax(this.form);"}}
      </form>
    </td>
    {{/if}}
  </tr>

  <tr>
    <th><strong>Risque Anesthésique</strong>: Antibioprophylaxie</th>
    <td style="text-align: center;">&mdash;</td>
    {{if $sejour->_id}}
    <td style="text-align: center;">
      <form name="editAntibioSejour" method="post" action="?">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="object_class" value="CSejour" />
        {{mb_field object=$dossier_medical_sejour field="risque_antibioprophylaxie" onchange="onSubmitFormAjax(this.form);"}}
      </form>
    </td>
    {{/if}}
  </tr>

  <tr>
    <th><strong>Risque Anesthésique</strong>: Thromboprophylaxie</th>
    <td style="text-align: center;">&mdash;</td>
    {{if $sejour->_id}}
    <td style="text-align: center;">
      <form name="editProphylaxieSejour" method="post" action="?">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="object_class" value="CSejour" />
        {{mb_field object=$dossier_medical_sejour field="risque_prophylaxie" onchange="onSubmitFormAjax(this.form);"}}
      </form>
    </td>
   {{/if}}
  </tr>

  <tr>
    <th>{{tr}}CDossierMedical-Eligible stay for outpatient care{{/tr}}</th>
    <td style="text-align: center;">&mdash;</td>
    {{if $sejour->_id}}
      <td style="text-align: center;">
        <form name="editPriseEnChargeAmbuSejour" method="post" action="?">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_sejour_aed" />
          {{mb_key object=$sejour}}
          {{mb_field object=$sejour field="pec_ambu" onchange="onSubmitFormAjax(this.form);"}}
        </form>
      </td>
    {{/if}}
  </tr>

  <tr>
    <th>
      {{mb_label object=$sejour field="rques_pec_ambu"}}
    </th>
    <td style="text-align: center;">&mdash;</td>
    {{if $sejour->_id}}
      <td>
        <form name="editRquesPriseEnChargeAmbuSejour" method="post" action="?">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_sejour_aed" />
          {{mb_key object=$sejour}}
          {{mb_field object=$sejour field="rques_pec_ambu" form="editRquesPriseEnChargeAmbuSejour" onchange="onSubmitFormAjax(this.form);"}}
        </form>
      </td>
    {{/if}}
  </tr>

  <tr>
    <td></td>
    <th>{{mb_label object=$dossier_medical_patient field="facteurs_risque"}}</th>
    <td style="text-align: center;">
      <form name="editFacteursRisque" method="post" action="?">
        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="object_id" value="{{$patient->_id}}" />
        <input type="hidden" name="object_class" value="CPatient" />
        {{mb_field object=$dossier_medical_patient field="facteurs_risque" onchange="onSubmitFormAjax(this.form);"
          form="editFacteursRisque" aidesaisie="validateOnBlur: 0" rows=5}}
      </form>
    </td>
    <td></td>
  </tr>
</table>