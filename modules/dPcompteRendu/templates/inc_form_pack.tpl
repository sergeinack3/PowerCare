{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}

<script>
  var listObjectClass = {{$listObjectClass|@json}};
  var aTraducClass = {{$listObjectAffichage|@json}};

  loadCategory = function(value) {
    var form = getForm("Edit-CPack");
    var select = $(form.category_id);
    var children = select.childElements();

    if (children.length > 0) {
      children[0].nextSiblings().invoke("remove");
    }

    // Insert new ones
    var cats = listObjectClass[$V(form.object_class)];

    if (!cats) {
      return;
    }

    var keys = Object.keys(cats);

    keys.each(function(key) {
      select.insert(DOM.option({value: key, selected: key == value}, cats[key]));
    });
  };
  Main.add(function() {
    loadCategory('{{$pack->category_id}}');
    var form = getForm("Edit-CPack");

    var urlUsers = new Url("mediusers", "ajax_users_autocomplete");
    urlUsers.addParam("edit", "1");
    urlUsers.addParam("input_field", "user_id_view");
    urlUsers.autoComplete(form.user_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.user_id, id);
      }
    });

    {{if $access_function}}
    var urlFunctions = new Url("mediusers", "ajax_functions_autocomplete");
    urlFunctions.addParam("edit", "1");
    urlFunctions.addParam("input_field", "function_id_view");
    urlFunctions.addParam("view_field", "text");
    urlFunctions.autoComplete(form.function_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.function_id, id);
      }
    });
    {{/if}}

    {{if $access_group}}
    var urlGroups = new Url("etablissement", "ajax_groups_autocomplete");
    urlGroups.addParam("edit", "1");
    urlGroups.addParam("input_field", "group_id_view");
    urlGroups.addParam("view_field", "text");
    urlGroups.autoComplete(form.group_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.group_id, id);
      }
    });
    {{/if}}
    {{if $pack->_id && !$pack->merge_docs}}
      Pack.toggleFusion();
    {{/if}}
  });
</script>

<form name="Edit-CPack" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$pack}}
  {{mb_key   object=$pack}}

  {{if !$pdf_and_thumbs}}
  <input type="hidden" name="fast_edit_pdf" value="{{$pack->fast_edit_pdf}}" />
  {{/if}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$pack}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$pack mb_field="user_id"}}
        {{mb_field object=$pack field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="user_id_view" value="{{$pack->_ref_user}}" />
      {{/me_form_field}}
    </tr>

    {{if $access_function}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$pack mb_field="function_id"}}
        {{mb_field object=$pack field=function_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="function_id_view" value="{{$pack->_ref_function}}" />
      {{/me_form_field}}
    </tr>
    {{/if}}

    {{if $access_group}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$pack mb_field="group_id"}}
        {{mb_field object=$pack field=group_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }"}}
        <input type="text" name="group_id_view" value="{{$pack->_ref_group}}" />
      {{/me_form_field}}
    </tr>
    {{/if}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$pack mb_field="nom"}}
        {{mb_field object=$pack field=nom style="width: 16em;"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 mb_object=$pack mb_field="object_class"}}
        <select name="object_class" style="width: 16em;" onchange="loadCategory(this.value); {{if $pack->_id}}Pack.changeClass(this); Pack.refreshListModeles();{{/if}}" class="{{$pack->_props.object_class}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$pack->_specs.object_class->_list item=object_class}}
            <option value="{{$object_class}}" {{if $object_class == $pack->object_class}}selected{{/if}}>
              {{tr}}{{$object_class}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$pack mb_field="merge_docs"}}
        {{mb_field object=$pack field=merge_docs onchange="Pack.toggleFusion(), Pack.changeTypeEligibleDocument(this.form)"}}
      {{/me_form_bool}}
    </tr>
    <tr id="CPack_category_id">
        {{me_form_field nb_cells=2 mb_object=$pack mb_field="category_id"}}
        <select name="category_id" style="width: 8em;">
          <option value="" {{if !$pack->category_id}}selected{{/if}}>&mdash; {{tr}}None|f{{/tr}}</option>
          {{foreach from=$listCategory item=currCat}}
            <option value="{{$currCat->file_category_id}}" {{if $currCat->file_category_id==$pack->category_id}}selected{{/if}}>{{$currCat->nom}}</option>
          {{/foreach}}
        </select>
        {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$pack mb_field="fast_edit"}}
        {{mb_field object=$pack field=fast_edit onchange="Pack.changeTypeEligibleDocument(this.form)"}}
      {{/me_form_bool}}
    </tr>

    {{if $pdf_and_thumbs}}
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$pack mb_field="fast_edit_pdf"}}
        {{mb_field object=$pack field=fast_edit_pdf canNull=false}}
      {{/me_form_bool}}
    </tr>
    {{/if}}

    <tr id="tr_eligible" {{if $pack->fast_edit || $pack->merge_docs}} style="display: none" {{/if}}>
      {{me_form_bool nb_cells=2 mb_object=$pack mb_field=is_eligible_selection_document}}
        {{mb_field object=$pack field=is_eligible_selection_document onchange="Pack.chooseDocument(this.value)"}}
      {{/me_form_bool}}
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $pack->_id}}
        <button class="modify" type="submit">
          {{tr}}Save{{/tr}}
        </button>
        <button class="trash me-tertiary" type="button" onclick="Pack.confirmDeletion(this.form);">
          {{tr}}Delete{{/tr}}
        </button>
        {{else}}
        <button class="submit" type="submit">
          {{tr}}Create{{/tr}}
        </button>
        {{/if}}
      </td>
    </tr>
  </table>
  
</form>

