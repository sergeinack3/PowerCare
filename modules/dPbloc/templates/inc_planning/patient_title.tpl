{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_colspan_patient value=2}}

{{if $_coordonnees}}
  {{math assign=_colspan_patient equation="x+1" x=$_colspan_patient}}
{{/if}}
{{if $_show_identity}}
  {{math assign=_colspan_patient equation="x+1" x=$_colspan_patient}}
{{/if}}
{{if $_display_main_doctor}}
  {{math assign=_colspan_patient equation="x+1" x=$_colspan_patient}}
{{/if}}
{{if $_display_allergy}}
  {{math assign=_colspan_patient equation="x+1" x=$_colspan_patient}}
{{/if}}
<th class="title" colspan="{{$_colspan_patient}}">
  Patient
</th>