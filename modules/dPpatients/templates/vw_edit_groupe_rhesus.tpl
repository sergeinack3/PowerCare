{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit_groupe_rhesus" method="post" onsubmit="return Patient.saveGroupeSanguin(this);">
  <table class="form">
      {{mb_class object=$dossier_medical}}
      {{mb_key   object=$dossier_medical}}
      <input type="hidden" name="patient_id" value="{{$patient_id}}"/>
    <tr>
      <th class="title" colspan="2">
          {{if $can_edit_groupe_rhesus}}
            {{tr}}Edit{{/tr}} {{tr}}date.from{{/tr}} {{tr}}CDossierMedical-groupe_sanguin-desc{{/tr}}
          {{else}}
              {{tr}}CDossierMedical-groupe_sanguin-desc{{/tr}}
          {{/if}}
      </th>
    </tr>
    <tr>
      <td>
          {{mb_label object=$dossier_medical field=groupe_sanguin}}
      </td>
      <td>
          {{if $can_edit_groupe_rhesus}}
              {{mb_field object=$dossier_medical field=groupe_sanguin}}
          {{else}}
              {{mb_value object=$dossier_medical field=groupe_sanguin}}
          {{/if}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$dossier_medical field=rhesus}}
      </td>
      <td>
          {{if $can_edit_groupe_rhesus}}
              {{mb_field object=$dossier_medical field=rhesus}}
          {{else}}
              {{mb_value object=$dossier_medical field=rhesus}}
          {{/if}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$dossier_medical field=phenotype}}
      </td>
      <td>
          {{if $can_edit_groupe_rhesus}}
              {{mb_field object=$dossier_medical field=phenotype}}
          {{else}}
              {{mb_value object=$dossier_medical field=phenotype}}
          {{/if}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
          {{if $can_edit_groupe_rhesus}}
            <button type="submit" class="save">{{if $dossier_medical->groupe_sanguin != "?" || $dossier_medical->rhesus != "?" || $dossier_medical->phenotype != ""}}{{tr}}Edit{{/tr}}{{else}}{{tr}}Save{{/tr}}{{/if}}</button>
          {{else}}
            <button type="button" onclick="Control.Modal.close()" class="close">{{tr}}Close{{/tr}}</button>
          {{/if}}
      </td>
    </tr>
  </table>
</form>
