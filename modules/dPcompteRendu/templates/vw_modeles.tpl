{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=modele}}

<script>
  function sortBy(order_col, order_way) {
    var oForm = getForm("filterModeles");
    $V(oForm.order_col, order_col);
    $V(oForm.order_way, order_way);
    oForm.onsubmit();
  }

  function updateSelected(elt) {
    elt.up("table").select("tr").invoke("removeClassName", "selected");
    elt.addClassName("selected");
  }

  function openModele(selected) {
    Modele.edit(selected.down(".id").getText());
  }

  Main.add(function() {
    var form = getForm("filterModeles");
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

    Modele.refresh();

    {{if $filtre->compte_rendu_id}}
      Modele.edit("{{$filtre->compte_rendu_id}}");
    {{/if}}

    new Url("compteRendu", "autocomplete")
      .addParam("mode_store", "0")
      .autoComplete(getForm("filterModeles").keywords_modele, '', {
        method: "get",
        minChars: 2,
        dropdown: true,
        width: "250px",
        callback: function(input, query) {
          var form = input.form;
          return query +
            "&object_class=" + $V(form.object_class) +
            "&user_id="      + $V(form.user_id)      +
            "&function_id="  + $V(form.function_id)  +
            "&type="         + $V(form.type);
        },
        updateElement: openModele
    });
  });
</script>

<div class="me-margin-bottom-8 me-margin-top-4">
  <a class="button new" href="#1" onclick="Modele.edit(0)">
    {{tr}}CCompteRendu-title-create{{/tr}}
  </a>
</div>

<div>
  <fieldset class="me-no-align">
    <legend>
      {{tr}}CCompteRendu-filter{{/tr}}
    </legend>

    <form name="filterModeles" method="get" onsubmit="return onSubmitFormAjax(this, null, 'modeles_area')">
      <input type="hidden" name="m" value="compteRendu" />
      <input type="hidden" name="a" value="ajax_list_modeles" />
      <input type="hidden" name="order_col" value="{{$order_col}}" />
      <input type="hidden" name="order_way" value="{{$order_way}}" />

      <table class="form me-no-box-shadow">
        <tr>
          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=user_id}}
            {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false); \$V(this.form.function_id_view, '', false); this.form.onsubmit();"}}
            <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
          {{/me_form_field}}

          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=object_class}}
            {{assign var=_spec value=$filtre->_specs.object_class}}
            <select name="object_class" onchange="this.form.onsubmit()">
              <option value="">&mdash; {{tr}}CCompteRendu-object_class-all{{/tr}}</option>
              {{foreach from=$_spec->_locales item=_locale key=_object_class}}
                <option value="{{$_object_class}}" {{if $filtre->object_class == $_object_class}}selected{{/if}}>{{$_locale}}</option>
              {{/foreach}}
            </select>
          {{/me_form_field}}

          <th class="me-padding-0"></th>
          {{me_form_field nb_cells=1}}
            <input type="text" placeholder="&mdash; {{tr}}CCompteRendu-modele-one{{/tr}}" name="keywords_modele" class="autocomplete me-placeholder str" />
          {{/me_form_field}}

          {{if $dmp_doc_types}}
            {{me_form_field nb_cells=2 mb_object=$document_item mb_field=type_doc_dmp}}
              <select name="type_dmp" onchange="this.form.onsubmit()">
                <option value="">&mdash; {{tr}}CDMPDocument.all{{/tr}}</option>
                {{foreach from=$dmp_doc_types key=_code item=_name}}
                  <option value="{{$_code}}">{{$_name}}</option>
                {{/foreach}}
              </select>
            {{/me_form_field}}
          {{/if}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=function_id}}
            {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false); this.form.onsubmit();"}}
            <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
          {{/me_form_field}}

          {{me_form_field nb_cells=2 mb_object=$filtre mb_field=type}}
            {{mb_field object=$filtre field=type onchange="this.form.onsubmit()" canNull=true emptyLabel="All"}}
          {{/me_form_field}}

          {{me_form_field nb_cells=4 mb_object=$filtre mb_field=actif}}
            {{mb_field object=$filtre field=actif typeEnum=select emptyLabel="All" onchange="this.form.onsubmit();"}}
          {{/me_form_field}}
        </tr>
      </table>
    </form>
  </fieldset>
</div>

<form name="deleteModele" method="post" class="prepared">
  {{mb_class class=CCompteRendu}}
  <input type="hidden" name="del" value="1" />
  <input type="hidden" name="compte_rendu_id" />
  <input type="hidden" name="compte_rendu_ids" />
</form>

<div id="modeles_area" class="me-no-border"></div>
