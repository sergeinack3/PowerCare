{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=liste_choix}}

<script>
  Main.add(function() {
    var form = getForm("Filter");
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "user_id_view")
      .autoComplete(form.user_id_view, null, {
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
    new Url("mediusers", "ajax_functions_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "function_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.function_id_view, null, {
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

    ListeChoix.filter();
  });
</script>

<div class="me-margin-bottom-8 me-margin-top-4">
  <button id="vw_idx_list_create_list_choix" class="new singleclick" onclick="ListeChoix.edit();">
    {{tr}}CListeChoix-title-create{{/tr}}
  </button>
</div>

<div>
  <form name="Filter" method="get" onsubmit="return ListeChoix.filter();">
    {{if !$access_function}}
      {{mb_field object=$filtre field=function_id hidden=1}}
    {{/if}}
    <table class="form me-no-align">
      <tr>
        <th class="category" colspan="4">{{tr}}Filter{{/tr}}</th>
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$filtre mb_field=user_id field_class="me-align-auto"}}
          {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false);
            if (this.form.function_id_view) {
              \$V(this.form.function_id_view, '', false);
            }
            this.form.onsubmit();"}}
          <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
        {{/me_form_field}}
        {{if $access_function}}
          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=function_id field_class="me-align-auto"}}
            {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false); this.form.onsubmit();"}}
            <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
          {{/me_form_field}}
        {{/if}}
      </tr>
    </table>
  </form>
</div>

<div id="list-listes_choix"></div>
 
