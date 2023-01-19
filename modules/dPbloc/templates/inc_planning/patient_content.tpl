{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Patient -->
{{if $_show_identity}}
  <td class="text">
    {{mb_include module=patients template=inc_patient_overweight patient=$patient float="right"}}

    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');" class="{{if $sejour->septique}}septique{{/if}}">
      {{$patient}}
    </span>
    {{if $_print_ipp && $patient->_IPP}}
      [{{$patient->_IPP}}]
    {{/if}}

    {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </td>
{{/if}}
{{if $_display_allergy}}
  <td class="text">
    {{if $patient->_ref_dossier_medical}}
      <ul>
      {{foreach from=$patient->_ref_dossier_medical->_ref_allergies item=_allergie}}
        <li>{{$_allergie->rques|spancate}}</li>
      {{/foreach}}
      </ul>
    {{/if}}
  </td>
{{/if}}
<td class="text">
  {{$patient->_age}}
  ({{mb_value object=$patient field=naissance}})
</td>
<td class="button">
  {{$patient->sexe|strtoupper}}
</td>
{{if $_coordonnees}}
<td>
  {{if $patient->tel}}
  {{mb_value object=$patient field="tel"}}
  <br />
  {{/if}}
  {{if $patient->tel2}}
  {{mb_value object=$patient field="tel2"}}
  {{/if}}
</td>
{{/if}}
{{if $_display_main_doctor}}
  <td>
    {{$patient->_ref_medecin_traitant}}
  </td>
{{/if}}