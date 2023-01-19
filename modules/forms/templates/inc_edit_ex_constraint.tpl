{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("editConstraint");

  adaptValueField(form.elements.operator);
  
  toggleObjectSelector(form.elements.field, form.elements.field);
  
  var url = new Url("forms", "ajax_autocomplete_hostfields");
  url.addParam("ex_class_event_id", "{{$ex_constraint->ex_class_event_id}}");
  url.autoComplete(form.elements._host_field_view, null, {
    minChars: 2,
    method: "get",
    //select: "view",
    dropdown: true,
    afterUpdateElement: function(field, selected){
      $V(field.form.elements.field, selected.get("value"));
      $V(field.form.elements._host_field_view, selected.down(".view").getText().strip());

      toggleObjectSelector(field, selected);
      
      /*if ($V(field.form.elements._host_field_view) == "") {
        $V(field.form.elements._host_field_view, selected.down('.view').innerHTML);
      }*/
    }
  });
});

adaptValueField = function(select) {
  var op = $V(select);
  var form = select.form;
  var valueElement = form.elements.value;

  if (op == "in" || op == 'notIn') {
    valueElement.rows = 15;
  }
  else {
    valueElement.rows = 1;
  }
};

toggleObjectSelector = function(input, selected) {
  var prop = $(selected).get("prop");
  var specType = prop.split(" ")[0];
  var dummy = DOM.input({className: prop});
  var spec = dummy.getProperties();
  var operator = input.form.elements.operator;
  var inOption = operator.down("option[value='in']") || operator.down("option[value='notIn']");
  var quickaccess = {{$host_quick_accesses|@json}};
  var reset = selected.name !== "field";

  var qa = $$(".quick-access").invoke("hide");

  if (quickaccess && quickaccess[$V(input.form.field)]) {
    var list = $H(quickaccess[$V(input.form.field)]);
    if (list.size()) {
      qa.each(function(q){
        q.show();
        var ul = q.down(".quick-access-classes ul");
        ul.update("");
        list.each(function(pair){
          ul.insert(DOM.li({}, DOM.strong({}, $T(pair.key)), " => provoquera un enregistrement de : ", DOM.strong({}, $T(pair.value))));
        });
      });
    }
  }
  
  $$('.specfield').invoke("disableInputs", reset);
  
  // if "selected" is not the input 
  if (reset) {
    $V(input.form.elements.value, "");
  }
  
  input.form.elements.value.enable();
  
  var specElements = $$('.spectype-'+specType);
  
  if (specElements.length == 0) {
    specElements = $$('.spectype-all');
  }
  
  specElements.invoke("enableInputs");
  
  switch (specType) {
    default:
      inOption.disabled = false;
      break;
    
    case "ref":
      $V(input.form._object_class, spec["class"]); // "class" is a reserved word !!!
      inOption.disabled = true;
      break;
      
    case "enum":
      var container = specElements[0];
      var options = {"":null}; // empty first element
      
      spec.list.each(function(v){
        options[v] = $T($(selected).get("field").replace(/(-)/g, ".")+"."+v);
      });
      
      var select = Form.Element.getSelect(options);
      $V(select, input.form.elements.value.value);
      select.observe("change", function(){ $V(this.form.elements.value, this.value); }.bind(select));
      container.update().insert(select);
      inOption.disabled = true;
      break;
  }
};

selectSugg = function(button) {
  var form = button.form;
  $V(form.elements.field, button.get("value")); 
  $V(form.elements._host_field_view, button.getText().strip());
  toggleObjectSelector(button, button);
};
</script>

<form name="editConstraint" method="post" action="?" onsubmit="return onSubmitFormAjax(this, ExConstraint.editCallback.curry({{$ex_constraint->ex_class_event_id}}))">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_constraint_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$ex_constraint}}
  {{mb_field object=$ex_constraint field=ex_class_event_id hidden=true}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ex_constraint colspan="2"}}
    
    <tr>
      <th style="width: 12em;">
        {{mb_label object=$ex_constraint field=field}}
      </th>
      <td>
        {{if $host_field_suggestions|@count}}
          <strong>Suggestions</strong>:<br />
          {{foreach from=$host_field_suggestions item=_sugg}}
            <button type="button" class="tick" data-value="{{$_sugg}}" data-field="{{$class_fields.$_sugg.field}}" data-prop="{{$class_fields.$_sugg.prop}}" onclick="selectSugg(this)">
              {{$class_fields.$_sugg.view}}
            </button><br />
          {{/foreach}}
          
          <br />
          <strong>Autres</strong>:<br />
        {{/if}}
        
        {{assign var=field value=$ex_constraint->field}}
        <input type="text" class="autocomplete" name="_host_field_view" value="{{$ex_constraint}}" size="60" />
        <input type="hidden" name="field" class="{{$ex_constraint->_props.field}}" tabIndex="1" 
               value="{{$ex_constraint->field}}" 
               data-prop="{{if $ex_constraint->_id}}{{$class_fields.$field.prop}}{{/if}}"
               data-field="{{if $ex_constraint->_id}}{{$class_fields.$field.field}}{{/if}}" />
      </td>
      
      {{* 
      <th>{{mb_label object=$ex_constraint field=_locale}}</th>
      <td>{{mb_field object=$ex_constraint field=_locale tabIndex="4"}}</td>
      *}}
    </tr>
    <tr>
      <td colspan="2">
        <hr />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$ex_constraint field=operator}}</th>
      <td>{{mb_field object=$ex_constraint field=operator tabIndex="2" onchange="adaptValueField(this)"}}</td>
      
      {{* 
      <th>{{mb_label object=$ex_constraint field=_locale_court}}</th>
      <td>{{mb_field object=$ex_constraint field=_locale_court tabIndex="5"}}</td>
      *}}
    </tr>
    <tr>
      <th>{{mb_label object=$ex_constraint field=value}}</th>
      <td>
        <div class="specfield spectype-all">
          {{mb_field object=$ex_constraint field=value tabIndex="3" prop="text" class="noresize" style="resize: none;" rows=1}}
        </div>
        
        <div class="specfield spectype-bool">
          <label>{{tr}}Yes{{/tr}} <input type="radio" name="_spectype_bool" value="1" {{if $ex_constraint->value === "1"}} checked="checked" {{/if}} onclick="$V(this.form.elements.value, this.value)" /></label>
          <label>{{tr}}No{{/tr}}  <input type="radio" name="_spectype_bool" value="0" {{if $ex_constraint->value === "0"}} checked="checked" {{/if}} onclick="$V(this.form.elements.value, this.value)" /></label>
        </div>
        
        <div class="specfield spectype-ref">
          {{if !$ex_constraint->_ref_target_object}}
            <div class="small-error">
              L'objet cible n'existe plus
            </div>
          {{else}}
            <input type="hidden" name="_object_class" value="{{$ex_constraint->_ref_target_object->_class}}" />
            <input type="text" name="_object_view" readonly="readonly" ondblclick="ObjectSelector.init()" value="{{$ex_constraint->_ref_target_object}}" size="60" />
            <button type="button" class="search notext" onclick="ObjectSelector.init()">{{tr}}Search{{/tr}}</button>
            <script type="text/javascript">
              ObjectSelector.init = function(){  
                this.sForm     = "editConstraint";
                this.sId       = "value";
                this.sView     = "_object_view";
                this.sClass    = "_object_class";
                this.onlyclass = "true";
                this.pop();
              };
              
              ObjectSelector.set = function(oObject) {
                var oForm = getForm(this.sForm);
                
                if (oForm.elements[this.sView]) {
                  $V(oForm.elements[this.sView], oObject.view);
                }
                
                $V(oForm.elements[this.sClass], oObject.objClass);
                $V(oForm.elements[this.sId], oObject.objClass+"-"+oObject.id);
              };
            </script>
          {{/if}}
        </div>
        
        <div class="specfield spectype-enum">
          
        </div>
      </td>
    </tr>

    <tbody class="quick-access" style="display: none;">
      <tr>
        <td colspan="2">
          <hr />
          <div class="quick-access-classes">
            <div class="small-info">
              Le déclenchement rapide rendra ce formulaire disponible depuis les évènements de type suivants :
              <ul></ul>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$ex_constraint field=quick_access}}</th>
        <td>{{mb_field object=$ex_constraint field=quick_access}}</td>
      </tr>
    </tbody>
      
    <tr>
      <th></th>
      <td colspan="1">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_constraint->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'la contrainte ',objName:'{{$ex_constraint->_view|smarty:nodefaults|JSAttribute}}'}, ExConstraint.editCallback.curry({{$ex_constraint->ex_class_event_id}}))">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
