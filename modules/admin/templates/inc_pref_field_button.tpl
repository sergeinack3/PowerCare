{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $var == "send_document_subject" }}
    {{mb_include template=inc_pref_field_str}}
{{/if}}
{{if $var == "send_document_body" }}
    {{mb_include template=inc_pref_field_text}}
{{/if}}
<button type="button" onclick="Preferences.editInput('form-edit-preferences_pref[{{$var}}]')">{{tr}}pref-button-add-patient{{/tr}}</button>

