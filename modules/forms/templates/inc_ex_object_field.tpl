{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value=editExObject}}
{{mb_default var=mode value=normal}}
{{mb_default var=is_predicate value=false}}
{{mb_default var=hide_label value=false}}

{{if !isset($ex_class|smarty:nodefaults)}}
  {{assign var=ex_class value=$ex_object->_ref_ex_class}}
{{else}}
  {{assign var=ex_object value=$ex_class->_ex_object}}
{{/if}}

{{assign var=show_label value=false}}
{{if !$hide_label}}
  {{assign var=show_label value=$ex_class->pixel_positionning}}
  {{if $show_label}}
    {{assign var=show_label value=$ex_field->show_label}}
  {{/if}}
  {{if $is_predicate}}
    {{assign var=show_label value=false}}
  {{/if}}
{{/if}}

{{assign var=_field_name value=$ex_field->name}}
{{assign var=_properties value=$ex_field->_default_properties}}
{{assign var=_spec value=$ex_object->_specs.$_field_name}}

{{if $mode == "normal" && is_countable($ex_field->_triggered_data) && $ex_field->_triggered_data|@count}}
  <script type="text/javascript">
  Main.add(function(){
    var form = getForm("{{$form}}");
    {{if $_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec'}}
      {{foreach from=$_spec->_list item=_value}}
        ExObject.initTriggers({{$ex_field->_triggered_data|@json}}, form, "_{{$_field_name}}_{{$_value}}", "{{$ex_object->_ref_ex_class->name}}", true);
      {{/foreach}}
    {{else}}
      ExObject.initTriggers({{$ex_field->_triggered_data|@json}}, form, "{{$_field_name}}", "{{$ex_object->_ref_ex_class->name}}");
    {{/if}}
  });
  </script>
{{/if}}

{{assign var=_style value=""}}
{{foreach from=$_properties key=_type item=_value}}
  {{if $_value != ""}}
    {{assign var=_style value="$_style $_type:$_value;"}}
  {{/if}}
{{/foreach}}

{{if !$show_label}}
  {{* On insère un mb_label pour que le checkForm donne un libellé lisible du champ *}}
  {{mb_label object=$ex_object field=$_field_name style="display:none;"}}
{{/if}}

{{if @$readonly}}
  {{if $conf.forms.CExClass.display_list_readonly && (($_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $_spec->typeEnum == "radio") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec' && $_spec->typeEnum == "checkbox") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec' && $_spec->typeEnum == "radio"))}}
    {{mb_include module=forms template=inc_ex_object_field_fieldset field_readonly=true}}
  {{else}}
    {{if $show_label}}
      {{mb_label object=$ex_object field=$_field_name}}: {{if $ex_object->$_field_name|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}}<br />{{/if}}
    {{/if}}

    {{$ex_field->prefix}}
    {{mb_value object=$ex_object field=$_field_name}}
    {{$ex_field->suffix}}
  {{/if}}

{{else}}
  {{assign var=field_readonly value=$ex_field->readonly}}
  {{if !$field_readonly}}
    {{assign var=field_readonly value=null}}
  {{/if}}

  {{assign var=field_emptyLabel value=$_spec->notNull|ternary:"\xA0":""}}

  {{if $field_readonly && $mode == "normal"}}
    {{if $conf.forms.CExClass.display_list_readonly && (($_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $_spec->typeEnum == "radio") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec' && $_spec->typeEnum == "checkbox") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec' && $_spec->typeEnum == "radio"))}}
      {{*Add the readonly class to avoid getting an error on readonly enum types*}}
      {{mb_field object=$ex_object field=$_field_name form=$form hidden=true readonly=true class='ex_class_field_readonly'}}

      {{mb_include module=forms template=inc_ex_object_field_fieldset}}
    {{else}}
      {{mb_field object=$ex_object field=$_field_name form=$form hidden=true class='ex_class_field_readonly'}}
      {{if $show_label}}
        {{if $ex_field->_ref_hypertext_links}}
          {{mb_include module=forms template=inc_vw_field_hypertext_links object=$ex_field ex_object=$ex_object field_name=$_field_name}}
        {{else}}
          {{mb_label object=$ex_object field=$_field_name}}
        {{/if}}
      {{/if}}

      {{$ex_field->prefix}}
      {{mb_value object=$ex_object field=$_field_name}}
      {{$ex_field->suffix}}
    {{/if}}

  {{else}}
    {{if $mode == "normal" && $_spec|instanceof:'Ox\Core\FieldSpecs\CRefSpec'}}
      {{mb_include module=forms template=inc_ex_object_field_autocomplete}}

    {{elseif ($_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $_spec->typeEnum == "radio") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec' && $_spec->typeEnum == "checkbox") || ($_spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec' && $_spec->typeEnum == "radio")}}
      {{mb_include module=forms template=inc_ex_object_field_fieldset}}

    {{elseif $ex_field->formula && !$is_predicate && !$_spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' && !$_spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec' && !$_spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec'}}
      {{mb_include module=forms template=inc_ex_object_field_formula}}

    {{elseif $_spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec' && $_spec->typeEnum == "select"}}
      {{mb_include module=forms template=inc_ex_object_field_select_multiple}}

    {{elseif $_spec|instanceof:'Ox\Core\FieldSpecs\CTextSpec' || ($_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $_spec->typeEnum == "select")}}
      {{mb_include module=forms template=inc_ex_object_field_two_lines}}

      {{* Custom handling of date fields is obsolete : the checkbox is pissing off everybody *}}
      {{*
      {{elseif $ex_class->pixel_positionning && !$is_predicate && ($_spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' || $_spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec' || $_spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec')}}
      {{mb_include module=forms template=inc_ex_object_field_date}}
      *}}

    {{else}}
      {{mb_include module=forms template=inc_ex_object_field_standard}}
    {{/if}}
  {{/if}}

{{/if}}
