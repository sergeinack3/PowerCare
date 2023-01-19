{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display_center value=1}}
{{mb_default var=float_right value=0}}
{{mb_default var=show_send_mail value=0}}
{{mb_default var=context_imagerie value=0}}
{{mb_default var=show_telemis value=0}}
{{mb_default var=load_js value=1}}
{{mb_default var=add_class value=""}}

{{if $load_js}}
  {{mb_script module=patients script=documentV2 ajax=true}}
{{/if}}

{{if $display_center || $float_right}}
<div style="{{if $display_center}}text-align: center; margin-top: 5px;{{elseif $float_right}}float: right;{{/if}}">
  {{/if}}
  <button type="button" class="thumbnails {{$add_class}} me-tertiary"
          onclick="DocumentV2.viewDocs('{{$patient_id}}', '{{$object->_id}}', '{{$object->_class}}');">{{tr}}common-Overview{{/tr}}</button>
  {{if $show_send_mail}}
    {{assign var=object_class_mail value=$object->_class}}
    {{assign var=object_id_mail value=$object->_id}}

    {{if $context_imagerie}}
      {{assign var=object_class_mail value=$context_imagerie->_class}}
      {{assign var=object_id_mail value=$context_imagerie->_id}}
    {{/if}}
    <button type="button" class="mail me-tertiary"
            onclick="Document.sendDocuments('{{$object_class_mail}}', '{{$object_id_mail}}');">
      {{tr}}CDocumentItem-action-send{{/tr}}
    </button>
  {{/if}}

  {{if "softway"|module_active && $context_imagerie}}
    {{mb_include module=softway template=inc_button_access_imagerie object=$context_imagerie}}
  {{/if}}
  {{if "telemis"|module_active && $show_telemis}}
      {{mb_include module=telemis template=inc_viewer_link patient=$object}}
  {{/if}}
  {{if $display_center || $float_right}}
</div>
{{/if}}
