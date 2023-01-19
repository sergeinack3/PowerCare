{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=context_guid value=""}}
{{mb_default var=show_text value=true}}
{{mb_default var=callback value="Prototype.emptyFunction"}}
{{mb_default var=context_copy_guid value=null}}

{{mb_script module=patients script=documentV2 ajax=1}}

{{if $context_copy_guid}}
  <button type="button" class="add me-primary {{if !$show_text}}notext{{/if}}"
          onclick="DocumentV2.copyDocItems('{{$context_copy_guid}}');">
    {{tr}}CDocumentItem-action-Copy selected documents{{/tr}}
  </button>
{{else}}
  <button type="button" class="add me-primary {{if !$show_text}}notext{{/if}}"
          onclick="DocumentV2.addDocument('{{$context_guid}}', '{{$patient_id}}', {{$callback}});">
    {{tr}}CPatient-action-add-document{{/tr}}
  </button>
{{/if}}
