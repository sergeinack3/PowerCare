{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_medical value=$selOp->_ref_sejour->_ref_patient->_ref_dossier_medical}}
{{assign var=constantes_medicales value=$selOp->_ref_sejour->_ref_patient->_ref_constantes_medicales}}

<fieldset>
  <legend><i class="fas fa-id-card"></i> {{tr}}CPatient.infos{{/tr}}</legend>
  <table class="main tbl">
    <tr>
      {{if "maternite"|module_active && $selOp->_ref_sejour->grossesse_id}}
        <th class="category">{{tr}}CGrossesse{{/tr}}</th>
      {{/if}}
      <th class="category">{{mb_label class=CConstantesMedicales field=poids}}</th>
      <th class="category">{{mb_label class=CConstantesMedicales field=taille}}</th>
      <th class="category">{{tr}}CAnesthPerop-Blood group / Rhesus-court{{/tr}}</th>
      <th class="category">{{mb_label class=CConsultAnesth field=rai}}</th>
      <th class="category">{{tr}}CConsultAnesth-mallampati{{/tr}}</th>
    </tr>
    <tr>
      {{if "maternite"|module_active && $selOp->_ref_sejour->grossesse_id}}
        <td class="me-text-align-left" style="text-align: center">{{tr var1=$selOp->_ref_sejour->_ref_grossesse->_semaine_grossesse}}CGrossesse-%s week(s) of amenorrhea{{/tr}}</td>
      {{/if}}
      <td class="me-text-align-left" style="text-align: center">
        {{if $constantes_medicales->poids}}
          {{$constantes_medicales->poids}} Kg
        {{else}}
          -
        {{/if}}
      </td>
      <td class="me-text-align-left" style="text-align: center">
        {{if $constantes_medicales->taille}}
          {{$constantes_medicales->taille}} cm
        {{else}}
          -
        {{/if}}
      </td>
      <td class="me-text-align-left" style="text-align: center">
        {{mb_value object=$dossier_medical field=groupe_sanguin}} {{mb_value object=$dossier_medical field=rhesus}}
      </td>
      <td class="me-text-align-left" style="text-align: center">
        {{mb_value object=$consult_anesth field=rai}}
      </td>
      <td class="me-text-align-left" style="text-align: center">{{mb_value object=$consult_anesth field=mallampati}}</td>
    </tr>
  </table>
</fieldset>

