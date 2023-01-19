{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file ajax=true}}
{{mb_script module=compteRendu script=document ajax=true}}
{{mb_script module=compteRendu script=modele_selector ajax=true}}

<script>
  Main.add(function() {
    var form = getForm("searchDoc");
    new Url("compteRendu", "autocomplete")
      .addParam("user_id", "{{$user_id}}")
      .addParam("function_id", "{{$function_id}}")
      .addParam("object_class", '{{$object_class}}')
      .autoComplete(form.keywords_modele, '', {
        method: "get",
        minChars: 2,
        afterUpdateElement: function(field, selected) {
          var compte_rendu_id = selected.down("div.id").getText();
          doAddDocContext(compte_rendu_id);
          $V(field, "");
        },
        dropdown: true,
        width: "250px"
      });

    new Url("compteRendu", "ajax_pack_autocomplete")
      .addParam("user_id", "{{$user_id}}")
      .addParam("function_id", "{{$function_id}}")
      .addParam("object_class", '{{$object_class}}')
      .autoComplete(form.keywords_pack, '', {
        minChars: 2,
        afterUpdateElement: function(field, selected) {
          var pack_id = selected.down("div.id").getText();
          doAddDocContext(null, pack_id);
          $V(field, "");
        },
        dropdown: true,
        width: "250px"
    });

    refreshDocsContext();
  });

  doAddDocContext = function(compte_rendu_id, pack_id) {
    var form = getForm("addDocContext");
    $V(form.compte_rendu_id, compte_rendu_id);
    $V(form.pack_id, pack_id);
    onSubmitFormAjax(form, refreshDocsContext);
  };

  doDelDocContext = function(compte_rendu_id) {
    var form = getForm("delDocContext");
    $V(form.compte_rendu_id, compte_rendu_id);
    onSubmitFormAjax(form, refreshDocsContext);
  };

  refreshDocsContext = function() {
    new Url("files", "ajax_list_docs_context")
      .addParam("context_doc_id", "{{$context_doc->_id}}")
      .requestUpdate("docs-context");
  };
</script>

<form name="addDocContext" method="post">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="dosql" value="do_add_doc_object" />
  <input type="hidden" name="object_class" value="{{$context_doc->_class}}" />
  <input type="hidden" name="object_id"    value="{{$context_doc->_id}}" />
  <input type="hidden" name="compte_rendu_id" />
  <input type="hidden" name="pack_id" />
</form>

<form name="delDocContext" method="post">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="dosql" value="do_modele_aed" />
  <input type="hidden" name="compte_rendu_id" />
  <input type="hidden" name="del" value="1" />
</form>

<table class="tbl">
  <tr>
    <td class="halfPane" style="vertical-align: top;">
      <form name="searchDoc" method="get">
        <input type="text" placeholder="&mdash; {{tr}}CCompteRendu-modele-one{{/tr}}" name="keywords_modele"
               class="autocomplete str" autocomplete="off" style="width: 8em;" />
        <input type="text" placeholder="&mdash; {{tr}}CPack{{/tr}}" name="keywords_pack"
               class="autocomplete str" autocomplete="off" style="width: 8em;"/>
      </form>

      <div id="docs-context"></div>
    </td>
    <td>
      <div id="files-context">
        <script>
          File.register('{{$context_doc->_id}}','{{$context_doc->_class}}', 'files-context');
        </script>
      </div>
    </td>
  </tr>
</table>
