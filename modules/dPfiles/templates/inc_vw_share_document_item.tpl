{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=synchronized value=false}}
{{assign var=selected     value=false}}
{{assign var=status       value=0}}
{{assign var=trad         value=""}}

{{assign var=file_traceability value=$receiver->_ref_file_traceability}}

{{if $_module_name == "sisra"}}
  {{if $docItem->_count_sisra_documents}}
    {{assign var=synchronized value=true}}
    {{assign var=selected     value=true}}
  {{elseif $file_traceability->_id}}
      {{if $file_traceability->sent_datetime}}
        {{assign var=synchronized value=true}}
        {{assign var=selected   value=true}}
      {{elseif $file_traceability->status == "pending"}}
        {{assign var=selected value=true}}
        {{assign var=status value=3}}
      {{elseif $file_traceability->status == "rejected"}}
        {{assign var=status value=5}}
      {{/if}}
  {{/if}}

  {{assign var=trad value="CFileTraceability.status.`$_module_name`.`$status`"}}

{{elseif $_module_name == "xds"}}
  {{if $docItem->_count_xds_documents}}
    {{assign var=synchronized value=true}}
  {{/if}}
{{elseif $_module_name == "appFineClient"}}
  {{if $docItem->_ref_appFine_idex && $docItem->_ref_appFine_idex->_id}}
    {{assign var=status       value=1}}
    {{assign var=synchronized value=true}}
    {{assign var=selected     value=true}}
  {{elseif $file_traceability->_id}}
    {{if $file_traceability->sent_datetime}}
      {{if $file_traceability->status == "sas_auto"}}
        {{assign var=synchronized value=true}}
      {{elseif $file_traceability->status == "rejected"}}
        {{assign var=status value=5}}
      {{/if}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "pending"}}
      {{assign var=status value=3}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "rejected"}}
        {{assign var=status value=5}}
    {{/if}}
  {{/if}}

  {{assign var=trad value="CFileTraceability.status.`$_module_name`.`$status`"}}

{{elseif $_module_name == "dmp"}}
  {{if $file_traceability->_id}}
    {{if $file_traceability->status == "pending"}}
      {{assign var=status value=3}}
    {{elseif $file_traceability->status == "rejected"}}
      {{assign var=status value=5}}
    {{else}}
      {{assign var=status value=$docItem->_status_dmp}}
    {{/if}}
  {{else}}
    {{assign var=status value=$docItem->_status_dmp}}
  {{/if}}

  {{if $docItem->_status_dmp == 1}}
    {{assign var=synchronized value=true}}
  {{/if}}

  {{assign var=trad value="CFileTraceability.status.`$_module_name`.`$status`"}}

{{elseif $_module_name == "oxSIHCabinet"}}
  {{if $docItem->_ref_sih_cabinet_idex && $docItem->_ref_sih_cabinet_idex->_id}}
    {{assign var=status       value=1}}
    {{assign var=synchronized value=true}}
    {{assign var=selected     value=true}}
  {{elseif $file_traceability->_id}}
    {{if $file_traceability->sent_datetime}}
      {{if $file_traceability->status == "sas_auto"}}
        {{assign var=synchronized value=true}}
        {{assign var=status value=1}}
      {{elseif $file_traceability->status == "rejected"}}
        {{assign var=status value=3}}
      {{/if}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "pending"}}
      {{assign var=status value=2}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "rejected"}}
      {{assign var=status value=3}}
    {{/if}}
  {{/if}}

  {{assign var=trad value="CFileTraceability.status.`$_module_name`.`$status`"}}

{{elseif $_module_name == "oxCabinetSIH"}}
  {{if $docItem->_ref_cabinet_sih_idex && $docItem->_ref_cabinet_sih_idex->_id}}
    {{assign var=status       value=1}}
    {{assign var=synchronized value=true}}
    {{assign var=selected     value=true}}
  {{elseif $file_traceability->_id}}
    {{if $file_traceability->sent_datetime}}
      {{if $file_traceability->status == "sas_auto"}}
        {{assign var=synchronized value=true}}
        {{assign var=status value=1}}
      {{elseif $file_traceability->status == "rejected"}}
        {{assign var=status value=3}}
      {{/if}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "pending"}}
      {{assign var=status value=2}}
      {{assign var=selected value=true}}
    {{elseif $file_traceability->status == "rejected"}}
      {{assign var=status value=3}}
    {{/if}}
  {{/if}}

  {{assign var=trad value="CFileTraceability.status.`$_module_name`.`$status`"}}
{{/if}}

<tr>
  <td>
    <input type="checkbox" class="input_receiver" name="receivers[]" {{if $selected}}disabled{{/if}} value="{{$receiver->_guid}}">
  </td>
  <td class="text">
    <img src="modules/{{$_module_name}}/images/icon.png" width="16"/> {{$receiver}}
  </td>
  <td class="text">
    {{mb_include module=system template=inc_vw_bool_icon value=$synchronized circle=true size="lg" ok_title=$trad ko_title=$trad}} {{tr}}{{$trad}}{{/tr}}
  </td>
</tr>
