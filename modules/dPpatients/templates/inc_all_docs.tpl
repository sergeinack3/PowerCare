{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ondblclick value=null}}

<script>
  Main.add(function () {
    $$('.doc_canceled').invoke('toggle');
  });
</script>

{{if $display === "list"}}
  <script>
    Main.add(function () {
      $("area_docs_{{$unique_all_docs}}").fixedTableHeaders();
    });
  </script>

<table class="tbl me-no-align">

  <thead>
  {{if $ondblclick}}
    <tr>
      <td colspan="6">
        <div class="small-info">
          {{tr}}CDocumentItem-Double click to select an item{{/tr}}
        </div>
      </td>
    </tr>
  {{/if}}

  <tr>
    <th class="section narrow"></th>
    <th class="section">
      {{mb_colonne class="CCompteRendu" field="nom" order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
    <th class="section narrow">
      {{mb_colonne class="CCompteRendu" field="author_id" order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
    <th class="section" style="width: 25%;">{{tr}}CCompteRendu-file_category_id{{/tr}}</th>
    <th class="section">{{tr}}CCompteRendu-object_id{{/tr}}</th>
    <th class="section narrow">
      {{mb_colonne class="CFile" field="file_date" order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
  </tr>
  </thead>

  <tbody>
  {{/if}}

  {{foreach from=$context->_all_docs.docitems item=_docs_by_context key=context}}
    {{if $tri !== "date"}}
      {{if $display === "list"}}
        <tr>
          <th colspan="6">
            {{$context}}
          </th>
        </tr>
      {{else}}
        <table class="tbl">
          <tr>
            <th>
              {{$context}}
            </th>
          </tr>
        </table>
      {{/if}}
    {{/if}}

    {{foreach from=$_docs_by_context item=_doc}}
      <div style="display: inline-block;">
        <div class="{{if !$_doc|instanceof:'Ox\Mediboard\System\Forms\CExLink' && $_doc->annule}}doc_canceled hatching{{/if}}">
          {{if $_doc|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu'}}
            {{mb_include module=compteRendu template=CCompteRendu_fileviewer doc=$_doc}}
          {{elseif $_doc|instanceof:'Ox\Mediboard\Files\CFile'}}
            {{if $ondblclick}}
              {{assign var=ondblclick_file value=$ondblclick|cat:'('|cat:$_doc->_id|cat:')'}}
            {{/if}}
            {{mb_include module=files template=CFile_fileviewer file=$_doc ondblclick=$ondblclick}}
          {{elseif $_doc|instanceof:'Ox\Mediboard\System\Forms\CExLink'}}
            {{mb_include module=forms template=CExLink_fileviewer link=$_doc}}
          {{/if}}
        </div>
      </div>
    {{/foreach}}
  {{foreachelse}}
    {{if $display === "list"}}
      <tr>
      <td colspan="6">
    {{/if}}
    <div class="small-info">
      {{tr}}CPatient-msg-document-none{{/tr}}
    </div>
    {{if $display === "list"}}
      </td>
      </tr>
    {{/if}}
  {{/foreach}}

  {{if $display === "list"}}
  </tbody>
</table>
{{/if}}
