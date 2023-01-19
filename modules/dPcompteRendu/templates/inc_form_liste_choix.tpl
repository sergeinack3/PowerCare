{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm("Edit");

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
  });
</script>

<form name="Edit" method="post" class="{{$liste->_spec}}" onsubmit="return ListeChoix.onSubmit(this)">
  {{mb_class object=$liste}}
  {{mb_key   object=$liste}}
  <input type="hidden" name="del" value="0" />

  {{if !$access_function}}
    {{mb_field object=$liste field=function_id hidden=1}}
  {{/if}}

  {{if !$access_group}}
    {{mb_field object=$liste field=group_id hidden=1}}
  {{/if}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$liste}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$liste mb_field=user_id}}
        {{mb_field object=$liste field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="user_id_view" value="{{$liste->_ref_user}}" />
      {{/me_form_field}}
    </tr>

    {{if $access_function}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$liste mb_field=function_id}}
        {{mb_field object=$liste field=function_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="function_id_view" value="{{$liste->_ref_function}}" />
      {{/me_form_field}}
    </tr>
    {{/if}}

    {{if $access_group}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$liste mb_field=group_id}}
        {{mb_field object=$liste field=group_id hidden=1
        onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }"}}
        <input type="text" name="group_id_view" value="{{$liste->_ref_group}}" />
      {{/me_form_field}}
    </tr>
    {{/if}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$liste mb_field=nom}}
        {{mb_field object=$liste field=nom}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 mb_object=$liste mb_field=compte_rendu_id}}
        <select name="compte_rendu_id" style="width: 20em;">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>

          {{foreach from=$modeles key=owner item=_modeles}}
          <optgroup label="{{$owners.$owner}}">
            {{foreach from=$_modeles item=_modele}}
            <option value="{{$_modele->_id}}" {{if $liste->compte_rendu_id == $_modele->_id}}selected{{/if}}>
              [{{tr}}{{$_modele->object_class}}{{/tr}}] {{$_modele->nom}}
            </option>
            {{foreachelse}}
            <option disabled>{{tr}}None{{/tr}}</option>
            {{/foreach}}
          </optgroup>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $liste->_id}}
        <button id="inc_form_list_choix_button_save" class="modify" type="submit">
          {{tr}}Save{{/tr}}
        </button>
        <button class="trash" type="button" onclick="ListeChoix.confirmDeletion(this)">
          {{tr}}Delete{{/tr}}
        </button>
        {{else}}
        <button id="inc_form_list_choix_button_create" class="submit" type="submit">
          {{tr}}Create{{/tr}}
        </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
