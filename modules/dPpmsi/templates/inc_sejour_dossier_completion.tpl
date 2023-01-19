{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=field value="reception_sortie"}}

{{assign var=sejour_id value=$sejour->_id}}
{{if $sejour->$field}}
  {{mb_label object=$sejour field=$field}} {{if $field != "reception_sortie"}}&nbsp;&nbsp;&nbsp;&nbsp;{{/if}}
  {{mb_field object=$sejour field=$field form="dossier_pmsi_selector" register=true onchange="PMSI.submitDossier('$sejour_id', '$field', this.value);"}}
{{else}}
  <button class="tick" type="button" onclick="PMSI.submitDossier('{{$sejour_id}}', '{{$field}}', 'now');">{{tr}}CSejour-{{$field}}{{/tr}}</button>
{{/if}}