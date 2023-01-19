{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var classes = {{$classes|@json}};

  var aTraduction = {};
  {{foreach from=$listTraductions key=key item=currClass}}
  aTraduction["{{$key}}"] = "{{$currClass|smarty:nodefaults}}";
  {{/foreach}}

  loadClasses = function(value) {
    var form = getForm("editFrm");
    var select = form.elements['class'];
    var options = classes;

    // delete all former options except first
    while (select.length > 1) {
      select.options[1] = null;
    }

    // insert new ones
    for (var elm in options) {
      var option = elm;
      if (typeof(options[option]) != "function") { // to filter prototype functions
        select.options[select.length] = new Option(aTraduction[option], option);
      }
    }

    $V(select, value);
    loadFields();
  };

  loadFields = function(value) {
    var form = getForm("editFrm");
    var select = form.elements['field'];
    var className  = form.elements['class'].value;
    var options = classes[className];

    // delete all former options except first
    while (select.length > 1) {
      select.options[1] = null;
    }

    // insert new ones
    for (var elm in options) {
      var option = elm;
      if (typeof(options[option]) != "function") { // to filter prototype functions
        select.options[select.length] = new Option($T(className+"-"+option), option);
      }
    }

    $V(select, value);
    loadDependances();
  };

  loadDependances = function(depend_value_1, depend_value_2) {
    var form = document.editFrm;
    var select_depend_1 = form.elements['depend_value_1'];
    var select_depend_2 = form.elements['depend_value_2'];
    var className  = form.elements['class'].value;
    var fieldName  = form.elements['field'].value;
    var options = classes[className];

    // delete all former options except first
    {{if !$aide->_is_ref_dp_1}}
    while (select_depend_1.length > 1) {
      select_depend_1.options[1] = null;
    }
    {{/if}}
    {{if !$aide->_is_ref_dp_2}}
    while (select_depend_2.length > 1) {
      select_depend_2.options[1] = null;
    }
    {{/if}}

    if (!options || !classes[className][fieldName]) {
      return;
    }

    {{if !$aide->_is_ref_dp_1}}
    // Depend value 1
    options_depend_1 = classes[className][fieldName]['depend_value_1'];
    for (var elm in options_depend_1) {
      var option = options_depend_1[elm];
      if (typeof(option) != "function") { // to filter prototype functions
        select_depend_1.options[select_depend_1.length] = new Option(aTraduction[option], elm, elm == depend_value_1);
      }
    }
    $V(select_depend_1, '{{$aide->depend_value_1}}');
    {{/if}}

    {{if !$aide->_is_ref_dp_2}}
    // Depend value 2
    options_depend_2 = classes[className][fieldName]['depend_value_2'];
    for (var elm in options_depend_2) {
      var option = options_depend_2[elm];
      if (typeof(option) != "function") { // to filter prototype functions
        select_depend_2.options[select_depend_2.length] = new Option(aTraduction[option], elm, elm == depend_value_2);
      }
    }
    $V(select_depend_2, '{{$aide->depend_value_2}}');
    {{/if}}
  };

  Main.add(function() {
    loadClasses('{{$aide->class}}');
    loadFields('{{$aide->field}}');
    loadDependances('{{$aide->depend_value_1}}', '{{$aide->depend_value_2}}');
    HyperTextLink.getListFor('{{$aide->_id}}', '{{$aide->_class}}');

    var form = getForm("editFrm");

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

<form name="editFrm" method="post" onsubmit="return onSubmitFormAjax(this, (function() {
    AideSaisie.removeLocalStorage();
    Control.Modal.close(); Aide.loadTabsAides(getForm('filterFrm'));
    }).bind(this));">
  {{mb_class object=$aide}}
  {{mb_key   object=$aide}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$aide}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$aide mb_field=user_id}}
        {{mb_field object=$aide field=user_id hidden=1
        onchange="
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="user_id_view" value="{{$aide->_ref_user}}" />
      {{/me_form_field}}
    </tr>

    {{if $access_function}}
      <tr>
        {{me_form_field nb_cells=2 mb_object=$aide mb_field=function_id}}
          {{mb_field object=$aide field=function_id hidden=1
          onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.group_id, '', false);
             if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
          <input type="text" name="function_id_view" value="{{$aide->_ref_function}}" />
        {{/me_form_field}}
      </tr>
    {{/if}}

    {{if $access_group}}
      <tr>
        {{me_form_field nb_cells=2 mb_object=$aide mb_field=group_id}}
          {{mb_field object=$aide field=group_id hidden=1
          onchange="
             \$V(this.form.user_id, '', false);
             \$V(this.form.user_id_view, '', false);
             \$V(this.form.function_id, '', false);
             if (this.form.function_id_view) {
               \$V(this.form.function_id_view, '', false);
             }"}}
          <input type="text" name="group_id_view" value="{{$aide->_ref_group}}" />
        {{/me_form_field}}
      </tr>
    {{/if}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$aide mb_field="class"}}
        <select name="class" class="{{$aide->_props.class}}" onchange="loadFields()" style="width: 12em;">
          <option value="">&mdash; {{tr}}CAideSaisie-Choose an object type{{/tr}}</option>
        </select>
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 mb_object=$aide mb_field="field"}}
        <select name="field" class="{{$aide->_props.field}}" onchange="loadDependances()" style="width: 12em;">
          <option value="">&mdash; {{tr}}CAideSaisie-Choose a field{{/tr}}</option>
        </select>
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 label="CAideSaisie-depend_value_1"}}
        {{if $aide->_is_ref_dp_1}}
          {{mb_field object=$aide field="depend_value_1" hidden=true}}
          <input type="hidden" name="_ref_class_depend_value_1" value="{{$aide->_class_dp_1}}" />
          <input type="text" name="_depend_value_2_view" value="{{$aide->_vw_depend_field_1}}" />
          <script>
            Main.add(function(){
              var form = getForm("editFrm");

              var url = new Url("system", "ajax_seek_autocomplete");
              url.addParam("object_class", $V(form._ref_class_depend_value_1));
              url.addParam("field", "depend_value_1");
              url.addParam("input_field", "_depend_value_1_view");
              url.addParam("show_view", "true");
              url.autoComplete(form.elements._depend_value_1_view, null, {
                minChars: 3,
                method: "get",
                select: "view",
                dropdown: true,
                afterUpdateElement: function(field, selected){
                  $V(field.form.elements.depend_value_1, selected.get("id"));
                }
              });
            });
          </script>
        {{else}}
          <select name="depend_value_1" class="{{$aide->_props.depend_value_1}}">
            <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
          </select>
        {{/if}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 label="CAideSaisie-depend_value_2"}}
        {{if $aide->_is_ref_dp_2}}
          {{mb_field object=$aide field="depend_value_2" hidden=true}}
          <input type="hidden" name="_ref_class_depend_value_2" value="{{$aide->_class_dp_2}}" />
          <input type="text" name="_depend_value_2_view" value="{{$aide->_vw_depend_field_2}}" />
          <script>
            Main.add(function(){
              var form = getForm("editFrm");

              var url = new Url("system", "ajax_seek_autocomplete");
              url.addParam("object_class", $V(form._ref_class_depend_value_2));
              url.addParam("field", "depend_value_2");
              url.addParam("input_field", "_depend_value_2_view");
              url.addParam("show_view", "true");
              url.autoComplete(form.elements._depend_value_2_view, null, {
                minChars: 3,
                method: "get",
                select: "view",
                dropdown: true,
                afterUpdateElement: function(field, selected){
                  $V(field.form.elements.depend_value_2, selected.get("id"));
                }
              });
            });
          </script>
        {{else}}
          <select name="depend_value_2" class="{{$aide->_props.depend_value_2}}">
            <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
          </select>
        {{/if}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 label="CAideSaisie-name"}}
        {{mb_field object=$aide field="name"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 label="CAideSaisie-text"}}
        {{mb_field object=$aide field="text"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field nb_cells=2 label="CHyperTextLink"}}
        <div id="list-hypertext_links" class="me-align-auto"></div>
      {{/me_form_field}}
    </tr>

      {{if "loinc"|module_active && $aide->_id}}
        <tr>
          <th>{{tr}}CLoinc-Loinc Codes{{/tr}}</th>
          <td>
              {{foreach from=$aide->_ref_codes_loinc item=_code name=count_code}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
              {{if !$smarty.foreach.count_code.last}},{{/if}}
            {{/foreach}}
          </td>
        </tr>
      {{/if}}

      {{if "snomed"|module_active && $aide->_id}}
        <tr>
          <th>{{tr}}CSnomed-Snomed Codes{{/tr}}</th>
          <td>
              {{foreach from=$aide->_ref_codes_snomed item=_code name=count_code}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
              {{if !$smarty.foreach.count_code.last}},{{/if}}
            {{/foreach}}
          </td>
        </tr>
      {{/if}}

    <tr>
      <td class="button" colspan="2">
        {{if $aide->aide_id}}
          <button class="modify me-primary" type="button" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form,{typeName:'l\'aide',objName:'{{$aide->name|smarty:nodefaults|JSAttribute}}'}, function() {
                    Control.Modal.close(); Aide.loadTabsAides(getForm('filterFrm'));
                    })">
            {{tr}}Delete{{/tr}}
          </button>

          {{if "loinc"|module_active || "snomed"|module_active}}
            <button type="button" title="{{tr}}CAideSaisie-Nomenclature|pl-desc{{/tr}}" onclick="Aide.showNomenclatures('{{$aide->_guid}}');">
              <i class="far fa-eye"></i> {{tr}}CAideSaisie-Nomenclature|pl{{/tr}}
            </button>
          {{/if}}

        {{else}}
          <button class="submit me-primary" type="button" onclick="this.form.onsubmit()">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>