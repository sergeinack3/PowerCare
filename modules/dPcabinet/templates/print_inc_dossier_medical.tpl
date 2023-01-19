{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="5">
    Dossier médical
    {{if $sejour->_NDA}}
     - [{{mb_value object=$sejour field="_NDA"}}]
    {{/if}}
  </th>
</tr>
  
<tr>
  <th style="width: 20%;">{{mb_label object=$consult field="_date"}}</th>
  <td>
    {{mb_value object=$consult field="_date"}} 
    {{mb_value object=$consult field="heure"}}
  </td>
  
  <th style="width: 25%">{{mb_label object=$consult field="motif"}}</th>
  <td>{{mb_value object=$consult field="motif"}}</td>
  {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
    <td style="margin-top: 4em;" class="title" rowspan="4">
        {{mb_include module=dPpatients template=vw_datamatrix_ins}}
    </td>
  {{/if}}
</tr>
  
<tr>
  
  <th>{{mb_label object=$patient field="tel"}}</th>
  <td>{{mb_value object=$patient field="tel"}}</td>
  
  <th>{{mb_label object=$patient field="adresse"}}</th>
  <td>{{mb_value object=$patient field="adresse"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$patient field="tel2"}}</th>
  <td>{{mb_value object=$patient field="tel2"}}</td>
  
  <th>{{mb_label object=$patient field="cp"}} - {{mb_label object=$patient field="ville"}}</th>
  <td>{{mb_value object=$patient field="cp"}} {{mb_value object=$patient field="ville"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$patient field="medecin_traitant"}}</th>
  <td>{{mb_value object=$patient field="medecin_traitant"}}</td>
  {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
    <th>{{tr}}CINSPatient{{/tr}}</th>
    <td>
      {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
    </td>
  {{/if}}
</tr>
