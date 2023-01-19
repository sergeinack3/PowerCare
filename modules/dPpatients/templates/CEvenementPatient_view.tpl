{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=evenement_patient register=true}}
{{if 'oxCabinet'|module_active}}
    {{mb_script module=oxCabinet script=appel_sih register=true}}
    {{mb_script module=oxCabinet script=timeline_implement register=true}}
    {{assign var=mode_DHE value="oxCabinet DHE mode_DHE"|gconf}}
{{/if}}
{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include template=CMbObject_view}}

{{if "loinc"|module_active && $object->_ref_codes_loinc|@count}}
  <table class="form">
    <tr>
      <th class="category">
        {{tr}}CLoinc-Loinc Codes{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        {{foreach from=$object->_ref_codes_loinc item=_code name=count_code}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
          {{if !$smarty.foreach.count_code.last}},{{/if}}
        {{/foreach}}
      </td>
    </tr>
  </table>
{{/if}}

{{if "snomed"|module_active && $object->_ref_codes_snomed|@count}}
  <table class="form">
    <tr>
      <th class="category">
        {{tr}}CSnomed-Snomed Codes{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        {{foreach from=$object->_ref_codes_snomed item=_code name=count_code}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
          {{if !$smarty.foreach.count_code.last}},{{/if}}
        {{/foreach}}
      </td>
    </tr>
  </table>
{{/if}}

<table class="form">
  <tr>
    <td class="button">
      {{if "loinc"|module_active || "snomed"|module_active}}
        <button type="button" title="{{tr}}CEvenementPatient-Nomenclature|pl-desc{{/tr}}" onclick="EvtPatient.showNomenclatures('{{$object->_guid}}');">
          <i class="far fa-eye"></i> {{tr}}CEvenementPatient-Nomenclature|pl{{/tr}}
        </button>
      {{/if}}

      <button type="button" class="edit"
      onclick="EvtPatient.editEvenements('{{$object->_ref_patient->_id}}','{{$object->_id}}');">{{tr}}Edit{{/tr}}</button>
        {{mb_include module=patients template=inc_button_add_doc context_guid=$object->_guid
        patient_id=$object->_ref_patient->_id
        callback="function(){EvtPatient.refreshContentEvenements('`$object->_ref_patient->_id`');}"}}
      {{if $object->_ref_sih_id400 && $object->_ref_context_id400->_id && $object->_type_sih == "MB"}}
        <button type="button" class="print" onclick="EvtPatient.printDHE({{$object->_id}});">{{tr}}Print{{/tr}}</button>
      {{/if}}
        {{if 'oxCabinet'|module_active && $object->_ref_sih_id400 && $object->_type_sih == "MB"}}
          <button type="button" class="edit"
                  onclick="AppelSIH.modifyDistantEvt('{{$object->_id}}', '{{$mode_DHE}}');">
              {{if $object->type == "sejour"}}
                  {{tr}}CSejour-title-modify{{/tr}}
              {{else}}
                  {{tr}}CEvenementPatient-modify operation{{/tr}}
              {{/if}}
          </button>
        {{/if}}
    </td>
  </tr>
</table>
