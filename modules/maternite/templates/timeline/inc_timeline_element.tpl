{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "pregnancy" || $type == "expected_term"}}
  {{mb_include module=maternite template=timeline/elements/inc_pregnancy item=$list[0]}}
{{/if}}

{{if $type == "birth"}}
  {{mb_include module=maternite template=timeline/elements/inc_birth}}
{{/if}}

{{if $type == "appointments" || $type == "anesth_appointments"}}
  {{mb_include module=maternite template=timeline/elements/inc_appointments}}
{{/if}}

{{if $type == "stays"}}
  {{mb_include module=maternite template=timeline/elements/inc_stays}}
{{/if}}

{{if $type == "surgeries"}}
  {{mb_include module=maternite template=timeline/elements/inc_surgeries}}
{{/if}}

{{if $type == "documents" || $type == "files" || $type == "forms"}}
  {{mb_include module=maternite template=timeline/elements/inc_documents}}
{{/if}}
