{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  copyDoc = function(doc_id) {
    new Url("compteRendu", "ajax_get_content", "raw")
      .addParam("doc_id", doc_id)
      .addParam("only_body", 1)
      .requestJSON(function(result) {
        Control.Modal.close();
        var editor = CKEDITOR.instances.htmlarea;
        editor.focus();
        editor.insertHtml(result);
      });
  }
</script>

{{assign var=copy_mode value=1}}

<table class="tbl">
  <tr>
    <th class="title">
      Documents de {{$patient}}
    </th>
  </tr>

  <tr>
    <td>
      <div class="small-info">
        Pour insérer le contenu d'un document, effectuez un double-clic sur le document de votre choix.
      </div>
    </td>
  </tr>

  {{if $patient->_ref_documents|@count}}
  <tr>
    <th>Contexte {{$patient}}</th>
  </tr>
  <tr>
    <td>
    {{foreach from=$patient->_ref_documents item=doc}}
      {{mb_include module=compteRendu template=CCompteRendu_fileviewer}}
    {{/foreach}}
    </td>
  </tr>
  {{/if}}

  {{foreach from=$patient->_ref_consultations item=_consultation}}
    {{if $_consultation->_ref_documents|@count}}
      <tr>
        <th>Consultation du {{$_consultation->_datetime|date_format:$conf.datetime}}</th>
      </tr>
      <tr>
        <td>
          {{foreach from=$_consultation->_ref_documents item=doc}}
            {{mb_include module=compteRendu template=CCompteRendu_fileviewer}}
          {{/foreach}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}

  {{foreach from=$patient->_ref_sejours item=_sejour}}
    {{if $_sejour->_ref_documents|@count}}
      <tr>
        <th>Séjour du {{$_sejour->entree|date_format:$conf.datetime}} au {{$_sejour->sortie|date_format:$conf.datetime}}</th>
      </tr>

      <tr>
        <td>
          {{foreach from=$_sejour->_ref_documents item=doc}}
            {{mb_include module=compteRendu template=CCompteRendu_fileviewer}}
          {{/foreach}}
        </td>
      </tr>
    {{/if}}

    {{foreach from=$_sejour->_ref_operations item=_operation}}
      {{if $_operation->_ref_documents|@count}}
        <tr>
          <th>Intervention du {{$_operation->date|date_format:$conf.datetime}}</th>
        </tr>

        <tr>
          <td>
            {{foreach from=$_operation->_ref_documents item=doc}}
              {{mb_include module=compteRendu template=CCompteRendu_fileviewer}}
            {{/foreach}}
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  {{/foreach}}
</table>