{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=pack}}

<script>
  Main.add(function() {
    Pack.refreshList();

    var form = getForm("Filter");
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

  });
</script>

<div class="me-margin-bottom-8 me-margin-top-4">
  <button class="new" onclick="Pack.edit('0');">
    {{tr}}CPack-title-create{{/tr}}
  </button>
</div>

<div>
  <form name="Filter" method="get" onsubmit="return Pack.filter();">
    <table class="form me-no-align">
      <tr>
         <th class="category" colspan="10">Filtrer les packs</th>
      </tr>

      <tr>
        {{me_form_field mb_object=$filtre mb_field=user_id nb_cells=2 field_class="me-margin-auto"}}
          {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false);
            if (this.form.function_id_view) {
              \$V(this.form.function_id_view, '', false);
            }
            this.form.onsubmit();"}}
          <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
        {{/me_form_field}}

        {{if $access_function}}
          {{me_form_field mb_object=$filtre mb_field=function_id nb_cells=2 field_class="me-margin-auto"}}
          {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false); this.form.onsubmit();"}}
            <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
          {{/me_form_field}}
        {{/if}}

        {{me_form_field mb_object=$filtre mb_field=object_class nb_cells=2 field_class="me-margin-auto"}}
          <select name="object_class" onchange="this.form.onsubmit();">
            <option value="">&mdash; Tous</option>
            {{foreach from=$classes key=_class item=_locale}}
              <option value="{{$_class}}" {{if $_class == $filtre->object_class}}selected{{/if}}>
                {{$_locale}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
    </table>
  </form>
</div>

<div id="list-packs"></div>
