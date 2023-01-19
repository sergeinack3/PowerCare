{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "appointments"}}
  {{mb_include module=soins template=timeline/elements/inc_appointment}}
{{/if}}

{{if $type == "anesth_appointments" || $type == "anesth_visits"}}
  {{mb_include module=soins template=timeline/elements/inc_anesth_appointment}}
{{/if}}

{{if $type == "documents" || $type == "files" || $type == "forms"}}
  {{mb_include module=soins template=timeline/elements/inc_document}}
{{/if}}

{{if $type == "movements" || $type == "arrived" || $type == "left" || $type == "assignment_begin" || $type == "assignment_end"}}
  {{mb_include module=soins template=timeline/elements/inc_movement}}
{{/if}}

{{if $type == "administer" || $type == "prescription_begin" || $type == "prescription_end"}}
  {{mb_include module=soins template=timeline/elements/inc_prescription}}
{{/if}}

{{if $type == "surgeries"}}
  {{mb_include module=soins template=timeline/elements/inc_surgery}}
{{/if}}

{{if $type == "vitals" || $type == "observations" || $type == "transmissions" || $type == "score"}}
  {{mb_include module=soins template=timeline/elements/inc_vital}}
{{/if}}
