{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_spec->typeEnum == "select"}}
  <div style="{{$_style}} display: inline-block; padding: 1px 3px;" defaultstyle="1">
    {{if $show_label}}
      <table class="main layout">
        <tr>
          <td class="narrow input-label">
            {{if $ex_field->_ref_hypertext_links}}
              {{mb_include module=forms template=inc_vw_field_hypertext_links object=$ex_field ex_object=$ex_object field_name=$_field_name}}
            {{else}}
              {{mb_label object=$ex_object field=$_field_name}}
            {{/if}}
          </td>
          <td>
            {{if $ex_class->pixel_positionning}}
              {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
            {{/if}}
            {{$ex_field->prefix}}
            {{mb_field
              object=$ex_object
              field=$_field_name
              form=$form
              emptyLabel=$field_emptyLabel
              readonly=$field_readonly
              tabindex=$ex_field->tab_index
            }}
            {{$ex_field->suffix}}
          </td>
        </tr>
      </table>
    {{else}}
      {{if $ex_class->pixel_positionning}}
        {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
      {{/if}}
      {{$ex_field->prefix}}
      {{mb_field
        object=$ex_object
        field=$_field_name
        form=$form
        emptyLabel=$field_emptyLabel
        readonly=$field_readonly
        tabindex=$ex_field->tab_index}}
      {{$ex_field->suffix}}
    {{/if}}
  </div>
{{else}}
  {{assign var=_field_class value="with-label"}}
  {{if !$show_label && !($ex_field->_ref_hypertext_links && ($ex_class->pixel_positionning || ($ex_field->coord_label_x == null && $ex_field->coord_label_y == null)))}}
    {{assign var=_field_class value="no-label"}}
  {{/if}}

  <fieldset style="{{$_style}} {{if !$ex_class->pixel_positionning && $_spec->typeEnum == 'radio' && !$field_readonly}} position: relative; {{/if}}" 
            defaultstyle="1" class="{{$_field_class}}">

    {{if $show_label}}
      <legend>
        {{if $ex_field->_ref_hypertext_links}}
          {{mb_include module=forms template=inc_vw_field_hypertext_links object=$ex_field ex_object=$ex_object field_name=$_field_name}}
        {{else}}
          {{mb_label object=$ex_object field=$_field_name}}
        {{/if}}

        {{if $ex_class->pixel_positionning}}
          {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
        {{/if}}
      </legend>
    {{else}}
      {{if $ex_field->_ref_hypertext_links && ($ex_class->pixel_positionning || ($ex_field->coord_label_x == null && $ex_field->coord_label_y == null))}}
        <legend class="not-printable">{{mb_include module=forms template=inc_vw_field_hypertext_links object=$ex_field}}</legend>
      {{/if}}
    {{/if}}

    {{if $_spec->typeEnum == 'radio' && !$field_readonly}}
      <div class="overlayed reset">
        <a href="#1" style="position: absolute; z-index: 20;"
           onclick="var elements = $A(getForm('{{$form}}').elements.{{$_field_name}}); if (isEmpty(elements)) { getForm('{{$form}}').elements.{{$_field_name}}.checked = false; getForm('{{$form}}').elements.{{$_field_name}}.fire('ui:change'); } else { elements.each(function(elt) { elt.checked = false; elt.fire('ui:change'); });} Event.stop(event);">
          <i class="fa fa-eraser form-picture-trigger" type="button" title="{{tr}}common-action-Reset{{/tr}}"></i>
        </a>
      </div>
    {{/if}}

    <div class="wrapper {{if $ex_class->pixel_positionning}}text{{/if}} {{if $_spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' && $_spec->columns > 1}} columns-{{$_spec->columns}} {{/if}}">
      {{$ex_field->prefix}}

      {{if $ex_field->report_class && !$ex_object->_id}}
        {{if array_key_exists($_field_name, $ex_object->_reported_fields)}}
          {{mb_field object=$ex_object field=$_field_name form=$form readonly=$field_readonly tabindex=$ex_field->tab_index data_reported_value=$ex_object->$_field_name}}
        {{else}}
          {{mb_field object=$ex_object field=$_field_name form=$form readonly=$field_readonly tabindex=$ex_field->tab_index}}
        {{/if}}
      {{else}}
        {{mb_field object=$ex_object field=$_field_name form=$form readonly=$field_readonly tabindex=$ex_field->tab_index}}
      {{/if}}

      {{$ex_field->suffix}}
    </div>
  </fieldset>
{{/if}}
