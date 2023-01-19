{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$patient->status}}
  {{mb_return}}
{{/if}}

{{mb_default var=float value='none'}}

<span class="texticon status-patient status-{{$patient->status|strtolower}} me-float-{{$float}}"
  onmouseover="ObjectTooltip.createDOM(this, 'patient_status_legend')">
  {{tr}}CPatient.status.{{$patient->status}}{{/tr}}
</span>

{{if $patient->_douteux}}
  <span class="texticon status-patient status-{{$patient->status|strtolower}} me-float-{{$float}}">
    {{tr}}CPatient-_douteux-court{{/tr}}
  </span>
{{/if}}

{{if $patient->_fictif}}
  <span class="texticon status-patient status-{{$patient->status|strtolower}} me-float-{{$float}}">
    {{tr}}CPatient-_fictif-court{{/tr}}
  </span>
{{/if}}

{{assign var=source_patient value=$patient->_ref_source_identite}}

{{if $source_patient && $source_patient->_ref_patient_ins_nir && $source_patient->_ref_patient_ins_nir->is_nia}}
  <span class="texticon ins-nia me-float-{{$float}}" title="{{tr}}CPatientINSNIR-is_nia{{/tr}}">
    NIA
  </span>
{{/if}}
{{mb_include module=dPpatients template=inc_tooltip_patient_status}}
