{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("{{$form}}");
  var url = new Url("system", "ajax_seek_autocomplete");
  url.addParam("object_class", "{{$_spec->class}}");
  url.addParam("field", "{{$_field_name}}");
  url.addParam("input_field", "_{{$_field_name}}_view");
  url.autoComplete(form.elements["_{{$_field_name}}_view"], null, {
    minChars: 3,
    method: "get",
    select: "view",
    dropdown: true,
    afterUpdateElement: function(field,selected){
      $V(field.form["{{$_field_name}}"], selected.getAttribute("id").split("-")[2]);
      if ($V(field.form.elements["_{{$_field_name}}_view"]) == "") {
        $V(field.form.elements["_{{$_field_name}}_view"], selected.down('.view').innerHTML);
      }
    }
  });
});
</script>
{{if $show_label}}
  <table class="layout">
    <tr>
      <td class="narrow input-label">{{mb_label object=$ex_object field=$_field_name}}</td>
      <td>
        {{$ex_field->prefix}}
        {{if $ex_class->pixel_positionning}}
          {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
        {{/if}}
          <input type="text" class="autocomplete" size="30"
           name="_{{$_field_name}}_view" style="{{$_style}}"
           value="{{$ex_object->_fwd.$_field_name}}"
           defaultstyle="1"
           {{if $ex_field->tab_index != null}} tabindex="{{$ex_field->tab_index}}" {{/if}}
          />
        {{$ex_field->suffix}}
      </td>
    </tr>
  </table>
{{else}}
  {{$ex_field->prefix}}
  {{if $ex_class->pixel_positionning}}
    {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
  {{/if}}
    <input type="text" class="autocomplete" size="30"
     name="_{{$_field_name}}_view" style="{{$_style}}"
     value="{{$ex_object->_fwd.$_field_name}}"
     defaultstyle="1"
     {{if $ex_field->tab_index != null}} tabindex="{{$ex_field->tab_index}}" {{/if}}
    />
  {{$ex_field->suffix}}
{{/if}}

{{mb_field object=$ex_object field=$_field_name form=$form hidden=true}}
