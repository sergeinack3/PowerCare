{{*
 * @package Mediboard\Livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr {{if $patient->deces}}class="hatching"{{/if}}>
  <th class="title text me-text-align-center" colspan="10">
      <span style="font-size: 1.5em;">
        {{$patient->_view}}
      </span>
  </th>
</tr>
<tr class="me-patient-banner-tamm-info">
  <td class="size-bandeau">
    {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
    {{assign var=rhesus value="?"}}

    {{if $dossier_medical->rhesus == "POS"}}
      {{assign var=rhesus value="+"}}
    {{elseif $dossier_medical->rhesus == "NEG"}}
      {{assign var=rhesus value="-"}}
    {{/if}}

    <strong>{{mb_title object=$dossier_medical field=groupe_sanguin}}/{{mb_title object=$dossier_medical field=rhesus}}:</strong>
    {{if $dossier_medical->groupe_sanguin && $rhesus != "?"}}
      {{mb_value object=$dossier_medical field=groupe_sanguin}} {{$rhesus}}
    {{else}}
      &mdash;
    {{/if}}
  </td>
  <td class="size-bandeau">
    <strong>{{mb_title object=$patient->_ref_constantes_medicales field=poids}}:</strong>
    {{if $patient->_ref_constantes_medicales->poids}}
      {{mb_value object=$patient->_ref_constantes_medicales field=poids}} kg
    {{else}}
      &mdash;
    {{/if}}
  </td>
  <td class="size-bandeau">
    <strong>{{mb_title object=$patient->_ref_constantes_medicales field=taille}}:</strong>
    {{if $patient->_ref_constantes_medicales->taille}}
      {{mb_value object=$patient->_ref_constantes_medicales field=taille}} cm
    {{else}}
      &mdash;
    {{/if}}
  </td>
  <td class="size-bandeau">
    <strong>{{mb_title object=$patient->_ref_constantes_medicales field=_imc}}:</strong>
    {{if $patient->_ref_constantes_medicales->_imc}}
      {{mb_value object=$patient->_ref_constantes_medicales field=_imc}}
    {{else}}
      &mdash;
    {{/if}}
  </td>
  <td class="size-bandeau">
    <strong>{{mb_title object=$patient field=naissance}}:</strong>
    {{mb_value object=$patient field=naissance}}
    ({{$full_age.locale}})
  </td>
  <td class="size-bandeau">
    <strong>{{mb_title object=$patient field=sexe}}:</strong>
    {{mb_value object=$patient field=sexe}}
  </td>
  {{if "maternite"|module_active && $patient->_ref_last_grossesse && $patient->_ref_last_grossesse->_id && !$patient->_ref_last_grossesse->datetime_cloture}}
    {{assign var=object_consult_id value=null}}
    {{assign var=show_checkbox     value=0}}

    {{if $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
      {{assign var=object_consult_id value=$object->_id}}
      {{assign var=show_checkbox     value=1}}
    {{/if}}
  {{/if}}
</tr>
