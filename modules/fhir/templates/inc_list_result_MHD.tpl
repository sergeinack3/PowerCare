{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $request_method === "POST"}}
  {{if $response_code == "201" || $code_status == "201"}}
    {{tr}}FHIR-msg-DocumentReference create{{/tr}}
   {{else}}
    {{if $error}}
      <div class="small-warning">{{$error}}</div>
    {{/if}}
    {{tr}}FHIR-msg-DocumentReference not create{{/tr}}
  {{/if}}
{{else}}
  <table class="tbl">
    <tr>
      <th class="section" colspan="100">{{$total}} résultats</th>
    </tr>

    {{if $resource_type == "DocumentReference"}}
      <tr>
        <th colspan="100">
          {{foreach from='Ox\Interop\Fhir\Profiles\CFHIR'|static:relation_map item=_icon key=_relation}}
            {{if $links && array_key_exists($_relation,$links)}}
              <button class="fa fa-{{$_icon}}" onclick="TestFHIR.requestWithURI('{{$links.$_relation}}', '{{$search_type}}')">
                {{$_relation}}
              </button>
            {{/if}}
          {{/foreach}}
        </th>
      </tr>

      <tr>
        <th class="narrow"></th>
        <th class="narrow">#</th>
        <th>{{tr}}CFile-file_date{{/tr}}</th>
        <th>{{tr}}CPatient{{/tr}}</th>
        <th>{{tr}}CSejour{{/tr}}</th>
        <th>{{tr}}CFile-file_name{{/tr}}</th>
        <th>{{tr}}CFile-annule{{/tr}}</th>
        <th>{{tr}}CFile-file_type{{/tr}}</th>
        <th>{{tr}}CFile-author_id{{/tr}}</th>
      </tr>

      {{foreach from=$results item=_file}}
        {{assign var=sejour value=$_file->_ref_object}}
        <tr>
          <td class="narrow">
            <button type="button" class="search notext" onclick="TestFHIR.showDocument('{{$_file->_fhir_resource_id}}')"></button>
          </td>
          <td class="narrow">{{$_file->_fhir_resource_id}}</td>
          <td>{{mb_value object=$_file field=file_date}}</td>
          <td>
            {{if $sejour}}
              {{assign var=patient value=$sejour->_ref_patient}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient->_view}}</span>
            {{else}}
              {{tr}}FHIR-msg-Object not found{{/tr}}
            {{/if}}
          </td>
          <td>
            {{if $sejour}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">{{$sejour->_view}}</span>
            {{else}}
              {{tr}}FHIR-msg-Object not found{{/tr}}
            {{/if}}
          </td>
          <td>{{mb_value object=$_file field=file_name}}</td>
          <td>{{mb_value object=$_file field=annule}}</td>
          <td>{{mb_value object=$_file field=file_type}}</td>
          <td>
            {{if $_file->_ref_author}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_file->_ref_author->_guid}}')">{{$_file->_ref_author->_view}}</span>
            {{else}}
              {{tr}}FHIR-msg-Object not found{{/tr}}
            {{/if}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CFile.none{{/tr}}</td>
        </tr>
      {{/foreach}}

    {{else}}
      <tr>
        <th class="narrow">#</th>
        <th>Master Identifier</th>
        <th>Statut</th>
        <th>References</th>
      </tr>

      {{foreach from=$results item=_doc_manifest}}
        <tr>
          <td class="narrow">{{$_doc_manifest->_fhir_resource_id}}</td>
          <td>{{mb_value object=$_doc_manifest field=repositoryUniqueID}}</td>
          <td>{{mb_value object=$_doc_manifest field=status}}</td>
          <td>
            <ul>
              {{foreach from=$_doc_manifest->_ref_documents_reference item=_link_doc_reference}}
                <li>{{$_link_doc_reference}}</li>
              {{/foreach}}
            </ul>
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CDocumentManifest.none{{/tr}}</td>
        </tr>
      {{/foreach}}

    {{/if}}
  </table>
{{/if}}
