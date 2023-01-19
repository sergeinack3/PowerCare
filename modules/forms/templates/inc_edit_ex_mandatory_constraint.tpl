{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("editConstraint");

    adaptValueField(form.elements.operator);
    toggleObjectSelector(form.elements.field);
  });

  adaptValueField = function (select) {
    var op = $V(select);
    var form = select.form;
    var valueElement = form.elements.value;

    if (op === "in") {
      valueElement.rows = 15;
    }
    else {
      valueElement.rows = 1;
    }
  };

  toggleObjectSelector = function (input) {
    var selected = input.options[input.selectedIndex];

    var prop = selected.get("prop");
    var specType = prop.split(" ")[0];
    var dummy = DOM.input({className: prop});
    var spec = dummy.getProperties();
    var operator = input.form.elements.operator;
    var inOption = operator.down("option[value='in']");

    var reference = $('constraint-reference_value');
    reference.hide();

    $$('.specfield').invoke("disableInputs");

    input.form.elements.value.enable();

    var specElements = $$('.spectype-' + specType);

    if (specElements.length === 0) {
      specElements = $$('.spectype-all');
    }

    specElements.invoke("enableInputs");

    switch (specType) {
      default:
        inOption.disabled = false;
        $V(input.form.elements.reference_value, '');
        break;

      case "ref":
        $V(input.form._object_class, spec["class"]); // "class" is a reserved word !!!
        inOption.disabled = true;
        $V(input.form.elements.reference_value, '');
        break;

      case "enum":
        var container = specElements[0];
        var options = {"": null}; // empty first element

        spec.list.each(function (v) {
          options[v] = $T($(selected).get("field").replace(/(-)/g, ".") + "." + v);
        });

        var select = Form.Element.getSelect(options);
        $V(select, input.form.elements.value.value);
        select.observe("change", function () {
          $V(this.form.elements.value, this.value);
        }.bind(select));
        container.update().insert(select);

        inOption.disabled = true;
        $V(input.form.elements.reference_value, '');
        break;

      case 'dateTime':
        reference.show();
        inOption.disabled = false;
        break;
    }
  };
</script>

<form name="editConstraint" method="post"
      onsubmit="return onSubmitFormAjax(this, ExConstraint.editCallback.curry({{$ex_constraint->ex_class_event_id}}));">
  {{mb_key object=$ex_constraint}}
  {{mb_class object=$ex_constraint}}
  <input type="hidden" name="del" value="0" />
  {{mb_field object=$ex_constraint field=ex_class_event_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ex_constraint colspan=2}}

    <tr>
      <th style="width: 12em;">
        {{mb_label object=$ex_constraint field=field}}
      </th>

      <td>
        {{assign var=field value=$ex_constraint->field}}

        <select name="field" onchange="toggleObjectSelector(this);">
          <option value="" data-prop="" data-field="">{{tr}}CExClassMandatoryConstraint-field.select{{/tr}}</option>

          {{foreach from=$mandatory_fields item=_field}}
            <option value="{{$_field}}" data-prop="{{$object->_specs.$_field}}" data-field="{{$object->_class}}.{{$_field}}"
              {{if $ex_constraint->field == $_field}} selected{{/if}}>
              {{tr}}{{$object->_class}}-{{$_field}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <hr />
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_constraint field=operator}}</th>
      <td>{{mb_field object=$ex_constraint field=operator tabIndex="2" onchange="adaptValueField(this)"}}</td>
    </tr>

    <tr id="constraint-reference_value">
      <th>{{mb_label object=$ex_constraint field=reference_value}}</th>
      <td>
        {{mb_field object=$ex_constraint field=reference_value emptyLabel='CExClassMandatoryConstraint-reference_value.select'}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_constraint field=value}}</th>
      <td>
        <div class="specfield spectype-all">
          {{mb_field object=$ex_constraint field=value tabIndex="3" prop="text" class="noresize" style="resize: none;" rows=1}}
        </div>

        <div class="specfield spectype-bool">
          <label>{{tr}}Yes{{/tr}} <input type="radio" name="_spectype_bool"
                                         value="1" {{if $ex_constraint->value === "1"}} checked{{/if}}
                                         onclick="$V(this.form.elements.value, this.value)" /></label>
          <label>{{tr}}No{{/tr}} <input type="radio" name="_spectype_bool"
                                        value="0" {{if $ex_constraint->value === "0"}} checked{{/if}}
                                        onclick="$V(this.form.elements.value, this.value)" /></label>
        </div>

        <div class="specfield spectype-enum">

        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_constraint field=comment}}</th>
      <td>{{mb_field object=$ex_constraint field=comment}}</td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_constraint->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{ajax:true,typeName:'la contrainte ',objName:'{{$ex_constraint->_view|smarty:nodefaults|JSAttribute}}'}, ExConstraint.editCallback.curry({{$ex_constraint->ex_class_event_id}}))">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
