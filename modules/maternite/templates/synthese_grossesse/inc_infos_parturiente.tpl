{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="4">{{tr}}CPatient-Patient information{{/tr}}</th>
</tr>
<tr>
  <th>{{tr}}CPatient|f{{/tr}}</th>
  <td><strong>{{$patient->nom}} {{$patient->prenom}}</strong></td>
  <th>{{mb_label object=$patient field=tel}}</th>
  <td>{{mb_value object=$patient field=tel}}</td>
</tr>
<tr>
  <th>{{mb_label object=$patient field=naissance}}</th>
  <td>
      {{mb_value object=$patient field=naissance}} ({{$patient->_age}})
  </td>
  <th>{{mb_label object=$patient field=tel2}}</th>
  <td>{{mb_value object=$patient field=tel2}}</td>
</tr>
<tr>
  <th></th>
  <td>
      {{mb_ternary var=last_taille test=$last_constantes.0->taille value=$last_constantes.0->taille other=$constantes_maman->taille}}
      {{mb_ternary var=last_poids  test=$last_constantes.0->poids  value=$last_constantes.0->poids  other=$constantes_maman->poids}}
      {{mb_ternary var=last_imc    test=$last_constantes.0->_imc   value=$last_constantes.0->_imc   other=$constantes_maman->_imc}}
    <strong>
        {{$last_taille}} cm &mdash; {{$last_poids}} kg
      &mdash; {{tr}}CConstantesMedicales-_imc{{/tr}} {{$last_imc}}
    </strong>
  </td>
  <th>{{mb_label object=$patient field=adresse}}</th>
  <td>
      {{mb_value object=$patient field=adresse}},
      {{mb_value object=$patient field=cp}} {{mb_value object=$patient field=ville}}
  </td>
</tr>
<tr>
  <th>{{tr}}CDossierMedical-medecin_traitant_id{{/tr}}</th>
  <td>{{$patient->_ref_medecin_traitant->_view}}</td>
</tr>
<tr>
  <th>{{tr}}CMedecin|pl{{/tr}}</th>
  <td>
      {{foreach from=$patient->_ref_medecins_correspondants item=_medecin_correspondant}}
          {{assign var=medecin value=$_medecin_correspondant->_ref_medecin}}
        - {{$medecin->_view}}
        <br/>
      {{/foreach}}
  </td>
</tr>
