{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .select_categories {
    width: 50%;
  }
</style>

<script>
  saveText = function (num = null) {
    textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
    values_input = [];

    fields_selected = document.getElementsByClassName('select_categories');
    for (var i = 0; i < fields_selected.length; i++) {
      if (num === i) {
        values_input.push("");
      } else {
        values_input.push(fields_selected[i].value);
      }
    }

    final_text = values_input.join("|");
    $V(textarea, final_text);
    return true;
  };
</script>

{{if $is_last}}
  {{assign var=values value="|"|explode:$value}}
  <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable">
    {{$value}}
  </textarea>
  {{tr}}EPrescription.type.PEDICURE{{/tr}} :
  <select {{if $is_inherited}}disabled{{/if}} class="select_categories" id="pedicure">
    <option value="">&mdash; {{tr}}CCategoryPrescription.select{{/tr}}</option>
    {{foreach from='Ox\Mediboard\Prescription\CCategoryPrescription::getCategories'|static_call:null item=_category}}
      <option value="{{$_category->_id}}" {{if $_category->_id == $values[0]}}selected{{/if}}>
        {{$_category}}
      </option>
    {{/foreach}}
  </select>
  <button class="trash notext" onclick="saveText(0)">{{tr}}Delete{{/tr}}</button>
  <br>
  {{tr}}EPrescription.type.ORTHOPHONISTE{{/tr}} :
  <select {{if $is_inherited}}disabled{{/if}} class="select_categories">
    <option value="">&mdash; {{tr}}CCategoryPrescription.select{{/tr}}</option>
    {{foreach from='Ox\Mediboard\Prescription\CCategoryPrescription::getCategories'|static_call:null item=_category}}
      <option value="{{$_category->_id}}"
              {{if $values|@count > 1}}{{if $_category->_id == $values[1]}}selected{{/if}}{{/if}}>
        {{$_category}}
      </option>
    {{/foreach}}
  </select>
  <button class="trash notext" onclick="saveText(1)">{{tr}}Delete{{/tr}}</button>
  <br>
  {{tr}}EPrescription.type.ORTHOPTISTE{{/tr}} :
  <select {{if $is_inherited}}disabled{{/if}} class="select_categories">
    <option value="">&mdash; {{tr}}CCategoryPrescription.select{{/tr}}</option>
    {{foreach from='Ox\Mediboard\Prescription\CCategoryPrescription::getCategories'|static_call:null item=_category}}
      <option value="{{$_category->_id}}"
              {{if $values|@count > 2}}{{if $_category->_id == $values[2]}}selected{{/if}}{{/if}}>
        {{$_category}}
      </option>
    {{/foreach}}
  </select>
  <button class="trash notext" onclick="saveText(2)">{{tr}}Delete{{/tr}}</button>
  <p style="text-align:center;">
    <button type="submit" onclick="return saveText()" class="save">{{tr}}Save{{/tr}}</button>
  </p>
{{else}}
  {{if $value}}
    {{$value}}
  {{/if}}
{{/if}}
