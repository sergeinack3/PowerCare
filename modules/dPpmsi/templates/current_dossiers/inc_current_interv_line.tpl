{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="compact">
    {{if $_operation->facture}}
      {{me_img src="tick.png" icon="tick" class="me-success" alt_tr="Ok"}}
    {{else}}
      {{me_img src="cross.png" alt="alerte" icon="cancel" class="me-error"}}
    {{/if}}
  </td>
  
  <td>
    {{assign var=sejour    value=$_operation->_ref_sejour}}
    {{assign var=sejour_id value=$sejour->_id}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
      <strong>{{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}</strong>
    </span>
  </td>
  
  <td class="text">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
  </td>

  <td class="text">
    {{assign var=patient value=$sejour->_ref_patient}}
    <a href="?m=pmsi&tab=vw_dossier_pmsi&patient_id={{$sejour->patient_id}}&sejour_id={{$sejour->_id}}">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
        {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
        {{$patient}}
      </span> 
    </a>
  </td>

  <td>
    {{mb_value object=$_operation field=date}}
  </td>

  <td>
    {{if $_operation->rank}}
      {{$_operation->time_operation|date_format:$conf.time}}
    {{else}}
      NP
    {{/if}}
  </td>

  <td class="text">
    {{mb_include module=planningOp template=inc_vw_operation}}
  </td>

  {{foreach from=$counter_type_documents.$sejour_id key=key_type item=_counter_type_document}}
    {{assign var=counter_docs value=$_counter_type_document.counter}}

    {{if $_counter_type_document.categorie_ids}}
      <td>
          <span {{if $counter_docs > 0}}onmouseover="ObjectTooltip.createDOM(this, 'show_details_files_{{$sejour_id}}_{{$key_type}}', {duration: 0});"{{/if}}>
            {{$counter_docs}}
          </span>

        <div id="show_details_files_{{$sejour_id}}_{{$key_type}}" style="display: none;">
          <table class="tbl">
            <tr>
              <th>{{tr}}mod-dPpatients-tab-vw_all_docs{{/tr}}</th>
            </tr>
            <tr>
              <td class="text docitem">
                <ul>
                  {{foreach from=$_counter_type_document.files item=_file}}
                    <li class="me-padding-5">
                      <span onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}');">
                        {{if $_file|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu'}}
                          {{mb_value object=$_file field=nom}}
                        {{else}}
                          {{mb_value object=$_file field=file_name}}
                        {{/if}}
                      </span>
                    </li>
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          </table>
        </div>
      </td>
    {{/if}}
  {{/foreach}}

  <td>
    {{mb_value object=$_operation field=labo}}
  </td>
  <td>
    {{mb_value object=$_operation field=anapath}}
  </td>
  <td>
    {{mb_include module=pmsi template=inc_counter_not_read context=$_operation}}
  </td>
</tr>
