{{*
* @package Mediboard\Provenance
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=provenance script=provenance}}
{{if $provenances|@count}}
  <th class="provenanceForm">
    {{mb_label object=$patient field=_provenance_id}}
  </th>
  <td class="provenanceForm">
  <span>
    {{if $patient->_ref_provenance_patient->_id !== null}}
      {{$patient->_ref_provenance_patient->_view}}
    {{else}}
      {{tr}}CProvenancePatient.none{{/tr}}
    {{/if}}
  </span>
    <button type="button" class="edit notext compact"
            onclick="Provenance.editProvenancePatient();">
    </button>
  </td>
  <tr class="provenanceForm" style="display: none">
    <th>
      {{mb_label object=$patient->_ref_provenance_patient field=provenance_id}}
    </th>
    <td>
      <select name="_provenance_id">
        <option value="0">{{tr}}None|f{{/tr}}</option>
        {{foreach from=$provenances item=_prov}}
          <option value="{{$_prov->_id}}" title="{{$_prov->desc}}"
                  {{if $patient->_ref_provenance_patient_patient->provenance_id === $_prov->_id}}selected{{/if}}>{{$_prov->libelle}}</option>
        {{/foreach}}
      </select>
    </td>
  </tr>
  <tr class="provenanceForm" style="display: none">
    <th>
      {{mb_label object=$patient field=_commentaire_prov}}
    </th>
    <td>
      {{mb_field object=$patient field=_commentaire_prov rows=3 class="noresize"}}
    </td>
  </tr>
{{/if}}
