{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=documents value=$object->_ref_documents}}
{{assign var=files     value=$object->_ref_files}}
{{assign var=presc     value=0}}

{{if "dPurgences"|module_active && "dPprescription"|module_active && "dPmedicament"|module_active && 'Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null != "besco"
  && $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation' && $object->sejour_id && in_array($object->_ref_sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$object->_ref_sejour->praticien_id)}}
  {{assign var=prescription value=$object->_ref_sejour->_ref_prescriptions.sejour}}
  {{if $prescription->_id}}
    {{assign var=presc value=1}}
  {{/if}}
{{/if}}

{{if !$documents|@count && !$files|@count && !$presc}}
  <div class="small-info">
    {{tr}}CCompteRendu.none{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<form name="selectDocsFrm" method="get" target="_blank">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="raw" value="print_docs" />

  <table class="main form">
    <tr>
      <th class="category" colspan="2">
        {{tr}}CCompteRendu-document{{/tr}}
      </th>
      <th class="category">
        {{tr}}CCompteRendu-date_print-desc{{/tr}}
      </th>
    </tr>
    {{foreach from=$documents item=curr_doc}}
      <tr>
        <th>
          {{$curr_doc->nom}}
        </th>
        <td>
          <input type="text" name="nbDoc[{{$curr_doc->_id}}]" size="2" value="{{$curr_doc->_nb_print}}" />
          <script>
            $(getForm("selectDocsFrm").elements['nbDoc[{{$curr_doc->_id}}]']).addSpinner({min:0});
          </script>
        </td>
        <td style="text-align: right;">
          {{if $curr_doc->date_print}}
            {{mb_value object=$curr_doc field=date_print}}
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
    {{foreach from=$files item=curr_file}}
    <tr>
      <th>
        {{$curr_file->file_name}}
      </th>
      <td>
        <input type="text" name="nbFile[{{$curr_file->_id}}]" size="2" value="1" />
        <script>
          $(getForm("selectDocsFrm").elements['nbFile[{{$curr_file->_id}}]']).addSpinner({min:0});
        </script>
      </td>
      <td></td>
    </tr>
    {{/foreach}}
    {{if $presc}}
      {{assign var=prescription value=$object->_ref_sejour->_ref_prescriptions.sortie}}
      {{if $prescription->_id}}
        <tr>
          <th>Prescription de sortie</th>
          <td>
            <input type="text" name="nbPresc[{{$prescription->_id}}]" size="2" value="1" />
            <script>
              $(getForm("selectDocsFrm").elements['nbPresc[{{$prescription->_id}}]']).addSpinner({min:0});
            </script>
          </td>
          <td></td>
        </tr>
      {{/if}}
    {{/if}}
    <tr>
      <td class="button" colspan="3">
        <button class="pdf">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
