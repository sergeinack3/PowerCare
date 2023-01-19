{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
showField = function(field_id, field_name, value){
  window.predicateFieldName = field_name;
  
  var form = getForm("ex_field_predicate-form");
  
  var url = new Url("forms", "ajax_view_ex_class_field");
  url.addParam("ex_class_field_id", field_id);
  url.addParam("form_name", "ex_field_predicate-form");
  url.addParam("value", value);
  url.requestUpdate("field-view", function(){
    // Link label with input
    var label = $$("#field-label label")[0];
    if (label) {
      label.htmlFor = field_name;
      label.id = null;
    }

    // Enable notNull on the field
    $$("#field-view input, #field-view select").each(function(element){
      element.addClassName("notNull");
    });

    form.removeClassName("prepared");
    prepareForm(form);

    toggleNoValue(form);
  });
};

toggleNoValue = function(form){
  var operator = $V(form.elements.operator);
  var fieldView = $("field-view");
  var fieldViewNoValue = $("field-view-novalue");

  if (operator == "hasValue" || operator == "hasNoValue") {
    fieldView.disableInputs();
    fieldView.select("input,textarea,select").invoke("removeClassName", "notNull");
    fieldViewNoValue.enableInputs();
  }
  else {
    fieldView.enableInputs();
    fieldView.select("input,textarea,select").invoke("addClassName", "notNull");
    fieldViewNoValue.disableInputs();
  }
};

predicateCallback = function(id, obj) {
  {{if $opener_field_value && $opener_field_view}}
    $V($("{{$opener_field_value}}"), id);
    $V($("{{$opener_field_view}}"), obj._view);
  {{else if $ex_field_predicate->ex_class_field_id}}
    ExField.edit("{{$ex_field_predicate->ex_class_field_id}}");
  {{/if}}
  
  Control.Modal.close();
};

Main.add(function(){
  var form = getForm("ex_field_predicate-form");
  
  if (form.elements._ex_field_view) {
    var url = new Url("forms", "ajax_autocomplete_ex_class_field");
    url.autoComplete(form.elements._ex_field_view, null, {
      minChars: 2,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected){
        var id = selected.get("id");
        var form = field.form;
        
        $V(form.ex_class_field_id, id);
        
        if (id) {
          showField(id, selected.down('.name').getText(), "");
        }
        
        if ($V(form.elements._field_view) == "") {
          $V(form.elements._field_view, selected.down('.view').getText());
        }
      },
      callback: function(input, queryString){
        return queryString + "&ex_class_id={{$ex_class->_id}}&exclude_ex_field_id={{$exclude_ex_field_id}}"; 
      }
    });
  }
  
  {{if $ex_field_predicate->ex_class_field_id}}
    var fieldName = "{{$ex_field_predicate->_ref_ex_class_field->name}}";
    showField("{{$ex_field_predicate->ex_class_field_id}}", fieldName, "{{$ex_field_predicate->value}}");
  {{/if}}
});
</script>

<form name="ex_field_predicate-form" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_field_predicate_aed" />
  <input type="hidden" name="callback" value="predicateCallback" />
  <input type="hidden" name="_compute_view" value="1" />
  <input type="hidden" name="del" value="" />
  {{mb_key object=$ex_field_predicate}}
  
  {{if $ex_field_predicate->ex_class_field_id}}
    {{mb_field object=$ex_field_predicate field=ex_class_field_id hidden=true}}
  {{/if}}
  
  <table class="main form">
    <tr>
      {{assign var=object value=$ex_field_predicate}}
      {{if $object->_id}}
      <th class="title modify text" colspan="4">
        {{mb_include module=system template=inc_object_idsante400}}
        {{mb_include module=system template=inc_object_history}}
        {{tr}}{{$object->_class}}-title-modify{{/tr}} 
        '{{$object}}'
      </th>
      {{else}}
      <th class="title text me-th-new" colspan="4">
        {{tr}}{{$object->_class}}-title-create{{/tr}} 
      </th>
      {{/if}}
    </tr>
    {{if !$ex_field_predicate->ex_class_field_id}}
    <tr>
      <th>{{mb_label object=$ex_field_predicate field=ex_class_field_id}}</th>
      <td>
        <input type="text" name="_ex_field_view" value="{{$ex_field_predicate->_ref_ex_class_field}}" size="40" />
        {{mb_field object=$ex_field_predicate field=ex_class_field_id hidden=true}}
      </td>
    </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$ex_field_predicate field=operator}}</th>
      <td>{{mb_field object=$ex_field_predicate field=operator onchange="toggleNoValue(this.form)"}}</td>
    </tr>
    <tr>
      <th id="field-label">{{mb_label object=$ex_field_predicate field=value}}</th>
      <td class="text">
        <div id="field-view">{{mb_field object=$ex_field_predicate field=value hidden=true}}</div>
        <div id="field-view-novalue" class="empty">
          <input type="hidden" name="value" value="__no_value__" disabled />
          N/A
        </div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_field_predicate->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'le prédicat ',objName:'{{$ex_field_predicate->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>