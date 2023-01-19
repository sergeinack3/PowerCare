{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=hyperTextLink}}
{{mb_script module=compteRendu script=aide}}

<script>
  function sortBy(order_col, order_way) {
    var form = getForm("filterFrm");
    $V(form.order_col_aide, order_col);
    $V(form.order_way, order_way);
    form.onsubmit();
  }

  var changePage = {};

  Main.add(function() {
    var form = getForm("filterFrm");

    Aide.loadTabsAides(form);

    ["user", "func", "etab"].each(function(type) {
      changePage[type] = function(page) {
        $V(form["start["+type+"]"], page);
      }
    });

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

<form name="deleteAide" method="post">
  {{mb_class class=CAideSaisie}}
  <input type="hidden" name="aide_id" />
</form>

<div class="me-margin-bottom-8 me-margin-top-4">
  <a href="#1" class="button new" onclick="Aide.edit(0)">{{tr}}CAideSaisie-title-create{{/tr}}</a>
</div>

<div>
  <form name="filterFrm" method="get" onsubmit="return Aide.loadTabsAides(this)">
    <input type="hidden" name="start[user]"     value="{{$start.user}}"     onchange="this.form.onsubmit()" />
    <input type="hidden" name="start[func]"     value="{{$start.func}}"     onchange="this.form.onsubmit()" />
    <input type="hidden" name="start[etab]"     value="{{$start.etab}}"     onchange="this.form.onsubmit()" />
    <input type="hidden" name="start[instance]" value="{{$start.instance}}" onchange="this.form.onsubmit()" />
    <input type="hidden" name="order_col_aide"  value="{{$order_col_aide}}" />
    <input type="hidden" name="order_way"       value="{{$order_way}}" />

    <table class="form me-no-align">
      <tr>
        <th class="category" colspan="10">{{tr}}CAideSaisie.filter{{/tr}}</th>
      </tr>

      <tr>
        {{me_form_field nb_cells=2 mb_object=$filtre mb_field=user_id}}
          {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false);
            if (this.form.function_id_view) {
              \$V(this.form.function_id_view, '', false);
            }
            this.form.onsubmit();"}}
          <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
        {{/me_form_field}}

        {{if $access_function}}
          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=function_id}}
            {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false); this.form.onsubmit();"}}
            <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
          {{/me_form_field}}
        {{/if}}

        {{me_form_field nb_cells=2 title_label="CAideSaisie-object_type-filter" label="common-Object type"}}
          <select name="class" onchange="this.form.onsubmit()" style="width: 12em;">
            <option value="">&mdash; {{tr}}CAideSaisie-object_type-all{{/tr}}</option>
            {{foreach from=$classes key=class item=fields}}
            <option value="{{$class}}" {{if $class == $filtre->class}}selected{{/if}}>
              {{tr}}{{$class}}{{/tr}}
            </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}

        {{me_form_field nb_cells=2 label="Keywords"}}
          <input type="text" name="keywords" value="{{$keywords}}" />
        {{/me_form_field}}

        <td>
          <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="tabs_aides"></div>
