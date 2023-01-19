{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation ajax=true}}

{{mb_default var=patient_id   value=""}}
{{mb_default var=sejour_id    value=""}}
{{mb_default var=operation_id value=""}}
{{mb_default var=grossesse_id value=""}}
{{mb_default var=consult_id   value=""}}
{{mb_default var=callback     value=""}}
{{mb_default var=class        value=""}}
{{mb_default var=me_primary   value=true}}
{{mb_default var=type         value="tous"}}

<button id="inc_vw_patient_button_consult_now" class="{{if $class}}fa fa-stethoscope{{else}}new{{/if}} me-margin-2 not-printable {{if $me_primary}}me-primary{{/if}}"
        onclick="Consultation.openConsultImmediate('{{$patient_id}}', '{{$sejour_id}}', '{{$operation_id}}', '{{$grossesse_id}}', '{{$callback}}', '{{$type}}', '{{$consult_id}}')">
  {{tr}}CConsultation-action-Immediate{{/tr}}
</button>
