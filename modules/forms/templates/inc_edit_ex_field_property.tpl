{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("ex_field_property-form");

  var e = form.elements._color_picker;
  e.colorPicker({
    change: function(color) {
      $V(form.elements.value, color ? color.toHexString() : '');
    }
  });

  typeSwitcher(form.elements.type);

  var url = new Url("forms", "ajax_autocomplete_ex_class_field_predicate");
  url.autoComplete(form.elements.predicate_id_autocomplete_view, null, {
    minChars: 2,
    method: "get",
    select: "view",
    dropdown: true,
    afterUpdateElement: function(field, selected){
      var id = selected.get("id");

      if (!id) {
        $V(field.form.predicate_id, "");
        $V(field.form.elements.predicate_id_autocomplete_view, "");
        return;
      }

      $V(field.form.predicate_id, id);

      if (id) {
        showField(id, selected.down('.name').getText());
      }

      if ($V(field.form.elements.predicate_id_autocomplete_view) == "") {
        $V(field.form.elements.predicate_id_autocomplete_view, selected.down('.view').getText());
      }
    },
    callback: function(input, queryString){
      return queryString + "&ex_class_id={{$ex_class->_id}}";
    }
  });

  form._value_size.addSpinner({step: 1, min: 6});
});

typeSwitcher = function(select){
  var type = $V(select);

  $$(".type-switch").invoke("hide").each(function(e){
    if (e.hasClassName(type)) {
      e.show();
      var element = e.down("input") || e;
      if (element.onchange) {
        element.onchange();
      }
    }
  });
};

propertyCallback = function(id, obj) {
  {{if $opener_field_value && $opener_field_view}}
    $V($("{{$opener_field_value}}"), id);
    $V($("{{$opener_field_view}}"), obj._view);
  {{else if $ex_field_property->object_id}}
    {{if $ex_field_property->object_class == "CExClassField"}}
      ExField.edit("{{$ex_field_property->object_id}}");
    {{elseif $ex_field_property->object_class == "CExClassMessage"}}
      ExMessage.edit("{{$ex_field_property->object_id}}");
    {{elseif $ex_field_property->object_class == "CExClassFieldSubgroup"}}
      ExSubgroup.edit("{{$ex_field_property->object_id}}");
    {{/if}}
  {{/if}}

  Control.Modal.close();
};
</script>

<form name="ex_field_property-form" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="callback" value="propertyCallback" />
  <input type="hidden" name="del" value="" />
  {{mb_class object=$ex_field_property}}
  {{mb_key object=$ex_field_property}}
  {{mb_field object=$ex_field_property field=object_id hidden=true}}
  {{mb_field object=$ex_field_property field=object_class hidden=true}}

  <table class="main form">
    <tr>
      {{assign var=object value=$ex_field_property}}
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

    <tr>
      <th>{{mb_label object=$ex_field_property field=type}}</th>
      <td>
        {{mb_field object=$ex_field_property field=type onchange="typeSwitcher(this)"}}

        {{mb_field object=$ex_field_property field=value hidden=true}}

        <span class="type-switch color background-color">
          <input type="hidden" name="_color_picker" value="{{if $ex_field_property->type == "color" || $ex_field_property->type == "background-color"}}{{$ex_field_property->value}}{{/if}}" />
        </span>

        {{foreach from='Ox\Mediboard\System\Forms\CExClassFieldProperty'|static:"_style_values" item=_values key=_type}}
          <select class="type-switch {{$_type}}" style="display: none;" onchange="$V(this.form.elements.value, $V(this))">
            {{foreach from=$_values item=_value}}
              <option value="{{$_value}}" {{if $ex_field_property->value == $_value}}selected{{/if}}>
                {{tr}}CExClassFieldProperty.value.{{$_type}}.{{$_value}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/foreach}}

        <span class="type-switch font-size">
          <input type="text" name="_value_size" onchange="$V(this.form.elements.value, $V(this)+'px')" value="{{if $ex_field_property->type == "font-size"}}{{$ex_field_property->value|floatval}}{{else}}11{{/if}}" size="2" /> pixels
        </span>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_field_property field=predicate_id}}</th>
      <td colspan="3">
        <input type="text" name="predicate_id_autocomplete_view" size="70" value="{{$ex_field_property->_ref_predicate->_view}}" />
        {{mb_field object=$ex_field_property field=predicate_id hidden=true}}
        {{*<button class="new notext" onclick="ExFieldPredicate.create('{{$ex_field_property->ex_class_field_id}}', null, this.form)" type="button">
          {{tr}}New{{/tr}}
        </button>*}}
      </td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button type="submit" class="submit singleclick">{{tr}}Save{{/tr}}</button>
        {{if $ex_field_property->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'le style ',objName:'{{$ex_field_property->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>