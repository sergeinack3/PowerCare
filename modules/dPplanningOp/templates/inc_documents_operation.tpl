{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ext_cabinet_id value=""}}

{{if @$preloaded}}
  {{assign var=patient_id value=$operation->_ref_sejour->patient_id}}
  {{assign var=object value=$operation}}
  <div class="me-float-none me-inline-block me-ws-wrap documentsV2-{{$object->_guid}} patient-{{$patient_id}} praticien-{{$object->chir_id}}" style="min-width: 200px; min-height: 50px; float: left; width: 50%;">
    {{mb_include module=patients template=inc_widget_documents}}
  </div>
  
  {{assign var=object value=$operation->_ref_sejour}}
  <div class="me-float-none me-inline-block me-ws-wrap documentsV2-{{$object->_guid}} patient-{{$patient_id}} praticien-{{$object->praticien_id}}" style="min-width: 200px; min-height: 50px; float: left; width: 50%;">
    {{mb_include module=patients template=inc_widget_documents}}
  </div>
{{else}}
  {{assign var=object value=$operation}}
  <div class="me-float-none me-inline-block me-ws-wrap" style="float: left; width: 50%;" id="Documents-{{$object->_guid}}">
    <script type="text/javascript">
    Document.register('{{$object->_id}}','{{$object->_class}}','{{$object->chir_id}}', 'Documents-{{$object->_guid}}', 'collapse', {ext_cabinet_id: '{{$ext_cabinet_id}}'});
    </script>
  </div>
  
  {{assign var=object value=$operation->_ref_sejour}}
  <div class="me-float-none me-inline-block me-ws-wrap" style="float: left; width: 50%;" id="Documents-{{$object->_guid}}">
    <script type="text/javascript">
    Document.register('{{$object->_id}}','{{$object->_class}}','{{$object->praticien_id}}', 'Documents-{{$object->_guid}}', 'collapse', {ext_cabinet_id: '{{$ext_cabinet_id}}'});
    </script>
  </div>
{{/if}}