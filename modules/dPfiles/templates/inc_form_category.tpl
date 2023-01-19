{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshList = function(type) {
    new Url("files", "ajax_list_owners_cat")
      .addParam("file_category_id", "{{$category->_id}}")
      .addParam("type", type).
      requestUpdate("list_" + type);
  };

  showDispatchDocs = function (cat_id) {
    new Url('files', 'vw_dispatch_docs')
    .addParam('cat_id', cat_id)
    .requestModal('50%', '50%');
  };

  Main.add(function() {
    {{if $category->_id}}
    var target = $('category_line_{{$category->_id}}');
    if (target) {
      target.addUniqueClassName('selected');
    }

    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "user_id_view")
      .autoComplete(getForm("addDefaultCatUser").user_id_view, "users_autocomplete", {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          var form = getForm("addDefaultCatUser");
          $V(form.owner_id, id);
          onSubmitFormAjax(form, function() {
            $V(form.user_id_view, "");
            $V(form.owner_id, "");
            refreshList("users");
          });
        }
      });

    new Url("mediusers", "ajax_functions_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "function_id_view")
      .addParam("view_field", "text")
      .autoComplete(getForm("addDefaultCatFunc").function_id_view, "functions_autocomplete", {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          var form = getForm("addDefaultCatFunc");
          $V(form.owner_id, id);
          onSubmitFormAjax(form, function() {
            $V(form.function_id_view, "");
            $V(form.owner_id, "");
            refreshList("functions");
          });
        }
      });

    refreshList("users");
    refreshList("functions");
    {{else}}
    $$("#list_file_categories tr").invoke("removeClassName", "selected");
    {{/if}}
  });
</script>

<form name="EditCat" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$category}}
  {{mb_key object=$category}}
  <input type="hidden" name="callback" value="FilesCategory.callback"/>

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$category}}

    <tr>
      <th>{{mb_label object=$category field=nom}}</th>
      <td>{{mb_field object=$category field=nom}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=nom_court}}</th>
      <td>{{mb_field object=$category field=nom_court}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=group_id}}</th>
      <td>
        <select name="group_id">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$groups item=_group}}
          <option value="{{$_group->_id}}" {{if $_group->_id == $category->group_id}}selected{{/if}}>
            {{$_group}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=importance}}</th>
      <td>{{mb_field object=$category field=importance}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=color}}</th>
      <td>{{mb_field object=$category field=color form="EditCat"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=send_auto}}</th>
      <td>{{mb_field object=$category field=send_auto}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=eligible_file_view}}</th>
      <td>{{mb_field object=$category field=eligible_file_view}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=medicale}}</th>
      <td>{{mb_field object=$category field=medicale}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=is_emergency_tab}}</th>
      <td>{{mb_field object=$category field=is_emergency_tab}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=class}}</th>
      <td>
        {{if $category->_count_doc_items}}
        {{tr}}{{$category->class|default:'All'}}{{/tr}}
        {{else}}
        <select name="class">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from=$listClass key=_class item=_class_view}}
            <option value="{{$_class}}" {{if $category->class == $_class}}selected{{/if}}>
              {{$_class_view}}
            </option>
          {{/foreach}}
        </select>
        {{/if}}
      </td>
    </tr>

    {{if "dmp"|module_active}}
      <tr>
        <th>{{mb_label object=$category field=type_doc_dmp}}</th>
        <td>
          <select name="type_doc_dmp" style="width: 295px;">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$category->_specs.type_doc_dmp->_list item=dmp_value}}
              <option value="{{$dmp_value}}" {{if $category->type_doc_dmp == $dmp_value}}selected{{/if}}>
                  {{tr}}CFile.type_doc_dmp.{{$dmp_value}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    {{if "sisra"|module_active}}
      <tr>
        <th>{{mb_label object=$category field=type_doc_sisra}}</th>
        <td>
          <select name="type_doc_sisra" style="width: 295px;">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$category->_specs.type_doc_sisra->_list key=sisra_key item=sisra_value}}
                <option value="{{$sisra_value}}" {{if $category->type_doc_sisra == $sisra_value}}selected{{/if}}>
                    {{tr}}CFile.type_doc_sisra.{{$sisra_value}}{{/tr}}
                </option>
              {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    <tr>
      <td class="button" colspan="2">
        {{if $category->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'la catégorie', objName: this.form.nom.value})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}

        {{if $can_dispatch}}
          <button class="fas fa-external-link-alt" type="button" onclick="showDispatchDocs('{{$category->_id}}');">
            {{tr}}CFilesCategory-Action-Dispatch{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

<table class="form">
  <tr>
    <th class="category" colspan="2">Objets liés</th>
  </tr>

  <tr>
    <th style="width:50%;"><strong>{{tr}}CFilesCategory-back-files{{/tr}}</strong></th>
    <td {{if !$category->_count_files}}class="empty"{{/if}}>{{$category->_count_files}}</td>
  </tr>

  <tr>
    <th><strong>{{tr}}CFilesCategory-back-documents{{/tr}}</strong></th>
    <td {{if !$category->_count_documents}}class="empty"{{/if}}>{{$category->_count_documents}}</td>
  </tr>
</table>

{{if $category->_id}}
<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      Catégorie par défaut
    </th>
  </tr>
  <tr>
    <th>
      Utilisateurs
    </th>
    <th>
      Fonctions
    </th>
  </tr>
  <tr>
    <th>
      <form name="addDefaultCatUser" method="post">
        {{mb_class class=CFilesCatDefault}}
        <input type="hidden" name="files_cat_default_id" />
        <input type="hidden" name="file_category_id" value="{{$category->_id}}" />
        <input type="hidden" name="object_class" value="{{$category->class}}" />
        <input type="hidden" name="owner_class" value="CMediusers" />
        <input type="hidden" name="owner_id" />
        <input type="text" name="user_id_view" placeholder="&mdash; Choisissez un utilisateur" />
        <div id="users_autocomplete" class="autocomplete" style="text-align: left; display: none;"></div>
      </form>
    </th>
    <th>
      <form name="addDefaultCatFunc" method="post">
        {{mb_class class=CFilesCatDefault}}
        <input type="hidden" name="files_cat_default_id" />
        <input type="hidden" name="file_category_id" value="{{$category->_id}}" />
        <input type="hidden" name="object_class" value="{{$category->class}}" />
        <input type="hidden" name="owner_class" value="CFunctions" />
        <input type="hidden" name="owner_id" />
        <input type="text" name="function_id_view" placeholder="&mdash; Choisissez une fonction" />
        <div id="functions_autocomplete" class="autocomplete" style="text-align: left; display: none;"></div>
      </form>
    </th>
  </tr>
  <tr>
    <td id="list_users" style="vertical-align: top; width: 50%;"></td>
    <td id="list_functions" style="vertical-align: top;"></td>
  </tr>
</table>
{{/if}}
