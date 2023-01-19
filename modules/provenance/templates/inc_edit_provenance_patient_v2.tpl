{{*
* @package Mediboard\Provenance
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=provenance script=provenance}}
{{if $provenances|@count}}
  <div class="categorie-form_fields-group">
    <div class="provenanceForm" style="display: block">
      {{me_form_field mb_object=$patient mb_field="_provenance_id"}}
        <div class="me-field-content{{if $patient->_ref_provenance_patient->_id === null}} empty{{/if}}">
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
        </div>
      {{/me_form_field}}
    </div>
    <div class="provenanceForm" style="display: none">
      {{me_form_field mb_object=$patient mb_field="_provenance_id"}}
        <select name="_provenance_id">
          <option value="0">{{tr}}None|f{{/tr}}</option>
          {{foreach from=$provenances item=_prov}}
            <option value="{{$_prov->_id}}" title="{{$_prov->desc}}"
                    {{if $patient->_ref_provenance_patient->provenance_id === $_prov->_id}}selected{{/if}}>{{$_prov->libelle}}</option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </div>
    <br>
    <div class="provenanceForm" style="display: none">
      {{me_form_field mb_object=$patient mb_field="_commentaire_prov"}}
      {{mb_field object=$patient field=_commentaire_prov rows=3 class="noresize"}}
      {{/me_form_field}}
    </div>
  </div>
{{/if}}