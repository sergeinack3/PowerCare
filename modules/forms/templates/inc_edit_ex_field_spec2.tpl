{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

ExConceptSpec.options = {{$spec->getOptions()|@json}};
booleanSpecs = {{$boolean|@json}};

updateFieldSpec = function(){
  var form = getForm("editFieldSpec");
  var fieldForm = getForm("editField");
  if (!checkForm(form)) return;

  var data = form.serialize(true);
  var fields = {};
  var specType = "{{$spec->getSpecType()}}";
  var str = specType;

  var notProps = {{'Ox\Mediboard\System\Forms\CExClassField'|static:'_property_fields_all'|@json}};

  var newData = {};
  Object.keys(data).each(function(k){
    if (data[k] !== "0" || booleanSpecs.indexOf(k) == -1) {
      newData[k] = data[k];
    }
  });
  data = newData;

  Object.keys(data).each(function(k){
    if (k.indexOf("__") === 0) {
      return;
    }

    var d = data[k];

    // The value is a CExClassField field
    if (notProps.indexOf(k) > -1) {
      $V(fieldForm[k], d);
      return;
    }

    if (d !== "") {
      if (Object.isArray(d)) {
        d = d.filter(function(e){return e !== ""});
        if (d.length == 0) return;
      }

      str += " "+(k.split("[")[0]);
      if (Object.isArray(d)) {
        var arr = d;
        if (k != "default" || ["set", "enum"].indexOf(specType) == -1) {
          arr = arr.invoke("replace", /\s/g, "\\x20").invoke("replace", /\|/g, "\\x7C");
        }
        str += "|"+arr.join("|");
      }
      else {
        var v = d.strip();
        if (booleanSpecs.indexOf(k) == -1 && (ExFieldSpec.options[k] != "bool" || v != "1")) {
          if (k != "default" || ["set", "enum"].indexOf(specType) == -1) {
            v = v.replace(/\s/g, "\\x20").replace(/\|/g, "\\x7C");
          }

          str += "|"+v;
        }
      }

      fields[k] = d;
    }
  });

  str = str.strip();
  ExConceptSpec.prop = str;

  var fieldForm = getForm("{{$form_name}}");
  $V(fieldForm.prop, str);
};

avoidSpaces = function(event) {
  var key = Event.key(event);
  // space or pipe
  if (key == 32 || key == 124) Event.stop(event);
};

cloneTemplate = function(input) {
  var template = $(input).up('table').down('.template');
  var clone = template.clone(true).observe("change", updateFieldSpec);
  template.insert({before: clone.show().removeClassName('template')});
  clone.down('input[type=text]').tryFocus();
};

confirmDelEnum = function(button) {
  if (!confirm("Voulez-vous vraiment supprimer cette valeur ? Elles seront supprimées de la base.")) return false;
  $(button).up("tr").remove();
  updateFieldSpec();
  return true;
};

updateTriggerData = function(trigger, value) {
  var fieldForm = getForm("{{$form_name}}");
  var trigger_data = $V(fieldForm._triggered_data);
  var trigger_object = trigger_data ? trigger_data.evalJSON() : {};

  if (trigger_object.length === 0) {
    trigger_object = {};
  }

  trigger_object[value] = trigger;

  $V(fieldForm._triggered_data, Object.toJSON(trigger_object));

  $("save-to-take-effect").show();
};

Main.add(function(){
  var form = getForm("editFieldSpec");

  form.select("input.nospace").invoke("observe", "keypress", avoidSpaces);
  form.select("input, select").invoke("observe", "change", updateFieldSpec).invoke("observe", "ui:change", updateFieldSpec);

  updateFieldSpec();
});
</script>

<form name="editFieldSpec" action="?" method="get" onsubmit="return false">

<table class="main form me-no-align me-no-box-shadow">
  <col class="narrow" />

  {{assign var=advanced_controls_limit value=50}}
  {{assign var=concept_based value=false}}

  {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField' && $context->concept_id}}
    {{assign var=concept_based value=true}}
  {{/if}}

  {{foreach from=$options item=_type key=_name name=specs}}
    {{if $smarty.foreach.specs.index == $advanced_controls_limit}}
      <tr>
        <th></th>
        <td>
          <button class="down" type="button" onclick="$(this).up('table').select('tr.advanced').invoke('toggle')">Plus d'options</button>
        </td>
      </tr>
    {{/if}}

    <tr {{if ($_name == "default" && $spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec') ||
             ($_name == "notNull" && $context|instanceof:'Ox\Mediboard\System\Forms\CExConcept') ||
             $smarty.foreach.specs.index >= $advanced_controls_limit}}class="advanced" style="display: none;"{{/if}}>
      <th><label for="{{$_name}}" title="{{$_name}}">{{tr}}CMbFieldSpec.{{$_name}}{{/tr}}</label></th>
      <td>
        {{assign var=spec_value value=$spec->$_name}}

        {{if !$concept_based}}
          {{* str *}}
          {{if $_type == "str"}}
            <input type="text" name="{{$_name}}" value="{{$spec_value|smarty:nodefaults|replace:"\\x20":" "|replace:"\\x7C":"|"}}"
                   class="str {{if $_name != "default"}}nospace pattern|\s*[a-zA-Z0-9_]*\s*{{/if}}" />

          {{* num *}}
          {{elseif $_type == "num"}}
            <script type="text/javascript">
              Main.add(function(){
                getForm("editFieldSpec")["{{$_name}}"].addSpinner();
              });
            </script>
            <input type="text" name="{{$_name}}" value="{{$spec_value}}" class="float" size="2" />

          {{* bool *}}
          {{elseif $_type == "bool"}}
            {{if !in_array($_name, $boolean)}}
              <label><input type="radio" name="{{$_name}}" value=""  {{if $spec_value === null || $spec_value === ""}}checked="checked"{{/if}} /> {{tr}}Undefined{{/tr}}</label>
            {{/if}}

            <label><input type="radio" name="{{$_name}}" value="0" {{if $spec_value === 0 || $spec_value === "0" || (($spec_value === null || $spec_value === "") && in_array($_name, $boolean))}}checked="checked"{{/if}} /> {{tr}}No{{/tr}}</label>
            <label><input type="radio" name="{{$_name}}" value="1" {{if $spec_value == 1}}checked="checked"{{/if}} /> {{tr}}Yes{{/tr}}</label>

          {{* enum *}}
          {{elseif is_array($_type)}}
            {{foreach from=$_type item=_type}}
            <label><input type="radio" name="{{$_name}}" value="{{$_type}}" {{if $spec_value === $_type}}checked="checked"{{/if}} /> {{tr}}CMbFieldSpec.{{$_name}}.{{$_type}}{{/tr}} </label>
            {{/foreach}}

          {{* field *}}
          {{elseif $_type == "field"}}
            {{if $other_fields|@count}}
              <select name="{{$_name}}">
                <option value=""> &mdash; </option>
                {{foreach from=$other_fields item=_other_field}}
                  <option value="{{$_other_field}}" {{if $_other_field == $spec_value}}selected="selected"{{/if}}>{{$_other_field}}</option>
                {{/foreach}}
              </select>
            {{else}}
              <input type="hidden" name="{{$_name}}" value="" />
              <span style="color: #999">Aucun autre champ</span>
            {{/if}}

          {{* list *}}
          {{elseif $_type == "list"}}

            {{* {{if $context instanceof CExConcept && $list_owner instanceof CExList}}

              <table class="tbl" style="width: 1%;">
                <col class="narrow" />

                <tr>
                  <th {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none;"{{/if}}>Valeur</th>
                  {{if $list_owner->coded}}
                    <th>Code</th>
                  {{/if}}
                  <th>Nom</th>
                </tr>

                {{foreach from=$spec->_list key=_key item=_value}}
                  <tr>
                    <td style="text-align: right; {{if $app->user_prefs.INFOSYSTEM == 0}}display: none;{{/if}}">
                      {{$_value}}
                      <input type="hidden" name="{{$_name}}[]" class="internal" value="{{$_value}}" />
                    </td>
                    {{if $list_owner->coded}}
                      <td>
                        {{$ex_list->_ref_items.$_value->code}}
                      </td>
                    {{/if}}
                    <td>{{$spec->_locales.$_value}}</td>
                  </tr>
                {{foreachelse}}
                  <tr>
                    <td {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none;"{{/if}}></td>
                    <td colspan="{{$list_owner->coded|ternary:3:2}}" class="empty">Aucun élément</td>
                  </tr>
                {{/foreach}}
              </table>

            {{else}} *}}

              {{if $context && $context->_id}}
                {{if $context == $list_owner}}

                  {{if $list_owner|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
                    {{foreach from=$context->_back.list_items item=_item}}
                      <input type="hidden" name="{{$_name}}[]" class="internal" value="{{$_item->_id}}" />
                    {{/foreach}}
                  {{else}}
                    {{foreach from=$spec->_list item=_value}}
                      <input type="hidden" name="{{$_name}}[]" class="internal" value="{{$_value}}" />
                    {{/foreach}}
                  {{/if}}
                {{/if}}
                <em>Voir "{{tr}}CExList-back-list_items{{/tr}}"</em>
              {{else}}
                <em>Enregistrez avant d'ajouter des élements</em>
              {{/if}}

            {{* {{/if}} *}}

          {{* class *}}
          {{elseif $_type == "class"}}
            <select name="{{$_name}}">
              {{foreach from=$classes item=_value}}
                <option value="{{$_value}}" {{if $_value == $spec->class}}selected="selected"{{/if}}>{{$_value}}</option>
              {{/foreach}}
            </select>

          {{/if}}

        {{else}} {{* concept based *}}

          {{if !(
            $_type == "list" ||
            $_type == "bool" && $_name == "default" ||
            $_type == "bool" && ($_name == "notNull" || $_name == "vertical") ||
            $_type == "num" && $_name == "columns"
          )}}
            <input type="hidden" name="{{$_name}}" value="{{$spec_value|smarty:nodefaults}}" />

            {{if $_name == "default" && ($spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' || $spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec' || $spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec')}}
              <button class="formula compact" type="button" onclick="Control.Tabs.activateTab('fieldFormulaEditor')">Saisir une formule</button>
            {{/if}}
          {{/if}}

          {{* str *}}
          {{if $_type == "str"}}
            {{$spec_value|smarty:nodefaults|replace:"\\x20":" "|replace:"\\x7C":"|"}}

          {{* num *}}
          {{elseif $_type == "num"}}
            {{if $_name == "columns"}}
              <script type="text/javascript">
                Main.add(function(){
                  getForm("editFieldSpec")["{{$_name}}"].addSpinner();
                });
              </script>
              <input type="text" name="{{$_name}}" value="{{$spec_value}}" class="float" size="2" />
            {{else}}
              {{$spec_value}}
            {{/if}}

          {{* bool *}}
          {{elseif $_type == "bool"}}
            {{if $_name == "notNull" ||
                 $_name == "vertical"}}
              {{if !in_array($_name, $boolean)}}
                <label><input type="radio" name="{{$_name}}" value=""  {{if $spec_value === null || $spec_value === ""}}checked="checked"{{/if}} /> {{tr}}Undefined{{/tr}}</label>
              {{/if}}

              <label><input type="radio" name="{{$_name}}" value="0" {{if $spec_value === 0 || $spec_value === "0" || (($spec_value === null || $spec_value === "") && in_array($_name, $boolean))}}checked="checked"{{/if}} /> {{tr}}No{{/tr}}</label>
              <label><input type="radio" name="{{$_name}}" value="1" {{if $spec_value == 1}}checked="checked"{{/if}} /> {{tr}}Yes{{/tr}}</label>
            {{else}}
              {{if !in_array($_name, $boolean)}}
                {{if $spec_value === null || $spec_value === ""}}{{tr}}Undefined{{/tr}}{{/if}}
              {{/if}}

              {{if $spec_value === 0 || $spec_value === "0" || (($spec_value === null || $spec_value === "") && in_array($_name, $boolean))}}{{tr}}No{{/tr}}{{/if}}
              {{if $spec_value == 1}}{{tr}}Yes{{/tr}}{{/if}}
            {{/if}}

          {{* enum *}}
          {{elseif is_array($_type)}}
            {{tr}}CMbFieldSpec.{{$_name}}.{{$spec_value}}{{/tr}}

          {{* field *}}
          {{elseif $_type == "field"}}
            {{$spec_value}}

          {{* list *}}
          {{elseif $_type == "list"}}

            {{* {{if $context instanceof CExConcept && $list_owner instanceof CExList}}

              <table class="tbl" style="width: 1%;">
                <col class="narrow" />

                <tr>
                  <th {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none;"{{/if}}>Valeur</th>
                  {{if $list_owner->coded}}
                    <th>Code</th>
                  {{/if}}
                  <th>Nom</th>
                </tr>

                {{foreach from=$spec->_list key=_key item=_value}}
                  <tr>
                    <td style="text-align: right; {{if $app->user_prefs.INFOSYSTEM == 0}}display: none;{{/if}}">
                      {{$_value}}
                      <input type="hidden" name="{{$_name}}[]" class="internal" value="{{$_value}}" />
                    </td>
                    {{if $list_owner->coded}}
                      <td>
                        {{$ex_list->_ref_items.$_value->code}}
                      </td>
                    {{/if}}
                    <td>{{$spec->_locales.$_value}}</td>
                  </tr>
                {{foreachelse}}
                  <tr>
                    <td {{if $app->user_prefs.INFOSYSTEM == 0}}style="display: none;"{{/if}}></td>
                    <td colspan="{{$list_owner->coded|ternary:3:2}}" class="empty">Aucun élément</td>
                  </tr>
                {{/foreach}}
              </table>

            {{else}} *}}

              {{if $context && $context->_id}}
                {{if $context == $list_owner}}
                  {{foreach from=$spec->_list key=_key item=_value}}
                    <input type="hidden" name="{{$_name}}[]" class="internal" value="{{$_value}}" />
                  {{/foreach}}
                {{/if}}
                <em>Voir "{{tr}}CExList-back-list_items{{/tr}}"</em>
              {{else}}
                <em>Enregistrez avant d'ajouter des élements</em>
              {{/if}}

            {{* {{/if}} *}}

          {{* class *}}
          {{elseif $_type == "class"}}
            {{$spec->class}}

          {{/if}}
        {{/if}}
      </td>
    </tr>
  {{/foreach}}

  {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
    {{foreach from=$context->getPropertyFields() item=_property_field}}
    <tr>
      <th>{{mb_label object=$context field=$_property_field}}</th>
      <td>
        {{if $context->_specs.$_property_field|instanceof:'Ox\Core\FieldSpecs\CNumSpec'}}
          {{mb_field object=$context field=$_property_field form=editFieldSpec increment=true size=3}}
        {{else}}
          {{mb_field object=$context field=$_property_field}}
        {{/if}}
      </td>
    </tr>
    {{/foreach}}
  {{/if}}
</table>

<div class="small-info" style="display: none;" id="save-to-take-effect">
  <strong>Enregistrez</strong> pour que la modification prenne effet
</div>

{{if $spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $context && $context->_id}}
  {{if $context == $list_owner}}
    </form>
    {{mb_include module=forms template=inc_ex_list_item_edit}}
  {{else}}
    {{mb_include module=forms template=inc_ex_list_item_subset}}
    </form>
  {{/if}}
{{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec' && $context && $context->_id && $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
  {{mb_include module=forms template=inc_ex_bool_triggers}}
  </form>
{{else}}
  </form>
{{/if}}


