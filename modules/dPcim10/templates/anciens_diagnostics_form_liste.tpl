{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=form_delete value=false}}

<form name="{{$object->_guid}}-cim-{{$code_type}}{{if $form_delete}}-delete{{/if}}" action="" method="post">
  {{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
    {{assign var=input_code_lib value=cim}}
    {{if $object->_ref_dossier_medical->_id}}
      {{assign var=dossier_medical value=$object->_ref_dossier_medical}}
      {{mb_class object=$dossier_medical}}
      {{mb_key object=$dossier_medical}}
    {{else}}
      <input type="hidden" name="m" value="patients" />
      <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
      <input type="hidden" name="object_class" value="CSejour" />
      <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
      <input type="hidden" name="del" value="0" />
    {{/if}}
  {{else}}
    {{assign var=input_code_lib value=$code_type|strtolower}}
    {{mb_key object=$object}}
    {{mb_class object=$object}}
  {{/if}}
  {{if $form_delete}}
    <input type="hidden" name="_deleted_code_{{$input_code_lib}}" value=""/>
  {{else}}
    <input type="hidden" name="_added_code_{{$input_code_lib}}" value=""/>
  {{/if}}
</form>
{{if !$form_delete}}
  {{mb_include module=cim10 template=anciens_diagnostics_form_liste form_delete=true}}
{{/if}}
