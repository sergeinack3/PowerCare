{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="request_mhd_provide_document" action="?" method="post" onsubmit="return TestFHIR.request(this, '{{$search_type}}')">
  <input type="hidden" name="request_method" value="POST" />

  <fieldset>
    <legend>{{tr}}fhir-msg-File found|pl{{/tr}}</legend>
    <table class="tbl">
      <tr>
        <th class="narrow"></th>
        <th class="narrow">Synchronisé</th>
        <th>{{tr}}CFile-file_name{{/tr}}</th>
        <th>{{tr}}CFile-file_date{{/tr}}</th>
        <th>{{tr}}CFile-file_type{{/tr}}</th>
        <th>{{tr}}CFile-type_doc_dmp{{/tr}}</th>
      </tr>
      {{foreach from=$sejour->_refs_docitems item=_doc}}
        <tr>
          <td>{{if !$_doc->_ref_fhir_idex->_id}}<input type="radio" name="object_guid" value="{{$_doc->_guid}}" />{{/if}}</td>
          <td style="text-align: center;">{{if $_doc->_ref_fhir_idex->_id}}<i class="fas fa-check" style="color: green;"></i>
            {{else}}<i class="fas fa-times" style="color:red;"></i>{{/if}}
          </td>
          {{if $_doc|instanceof:'Ox\Mediboard\Files\CFile'}}
            <td>{{mb_value object=$_doc field=file_name}}</td>
            <td>{{mb_value object=$_doc field=file_date}}</td>
            <td>{{mb_value object=$_doc field=file_type}}</td>
            <td>{{mb_value object=$_doc field=type_doc_dmp}}</td>
          {{else}}
            <td>{{mb_value object=$_doc field=nom}}</td>
            <td>{{mb_value object=$_doc field=creation_date}}</td>
            <td>application/pdf</td>
            <td>{{mb_value object=$_doc field=type_doc_dmp}}</td>
          {{/if}}
        </tr>
        {{foreachelse}}
        <tr>
          <td>
            <div class="small-info">{{tr}}No-file{{/tr}}</div>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </fieldset>

  <table class="form">
    <tr>
      <td>
        <fieldset>
          <legend>Fichier à envoyer - ITI-65</legend>
          <table class="form">
            <tr>
              <th><label for="response_type" title="Format de la réponse">Format de la requête</label></th>
              <td>
                <label for="request_type">fhir+json</label>
                <input tabindex="13" type="radio" name="request_type" value="fhir+json" />
                <label for="request_type_xml">fhir+xml</label>
                <input tabindex="14" type="radio" name="request_type" value="fhir+xml" checked/>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <button class="send singleclick">
          {{tr}}Send{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>