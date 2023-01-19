{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !isset($print|smarty:nodefaults)}}
  <a href="#1" onclick="{{$rpu_link}}">
{{else}}
  <div>
{{/if}}
  <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
    <big class="CPatient-view">{{$patient}}</big> 
  </strong>

  {{mb_include module=patients template=inc_icon_bmr_bhre}}
{{if !isset($print|smarty:nodefaults)}}
  </a>
{{else}}
  </div>
{{/if}}
{{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
({{$patient->sexe|upper}})
{{if $conf.dPurgences.age_patient_rpu_view}}{{$patient->_age}}{{/if}}

{{if $sejour->_ref_prescription_sejour}}
  <div class="text" style="font-size: 12pt;">
    {{mb_include module=prescription template=vw_line_important lines=$sejour->_ref_prescription_sejour->_ref_lines_important}}
  </div>
{{/if}}