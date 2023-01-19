{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

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

      <td style="text-align: right;">
        {{if $ex_class->pixel_positionning}}
          {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
        {{/if}}

        {{$ex_field->prefix}}

        {{assign var=_size value=null}}
        {{if $ex_class->pixel_positionning}}
          {{assign var=_size value=''}}
        {{else}}
          {{assign var=_spec value=$ex_field->_ref_concept->_concept_spec}}
          {{if $_spec|instanceof:'Ox\Core\FieldSpecs\CFloatSpec'}}
            {{assign var=_size value=4}}
          {{/if}}
        {{/if}}

          {{mb_field
            object=$ex_object
            field=$_field_name
            register=true
            increment=true
            form=$form
            emptyLabel=$field_emptyLabel
            style=$_style
            defaultstyle=1
            readonly=$field_readonly
            tabindex=$ex_field->tab_index
            size=$_size
          }}

          {{mb_include module=forms template=inc_ex_field_link_formula ex_object=$ex_object ex_field=$ex_field spec=$_spec}}
        {{$ex_field->suffix}}

        {{if $conf.forms.CExConcept.native_field}}
          {{assign var=_concept value=$ex_field->_ref_concept}}

          {{if $_concept && $_concept->native_field}}
            {{if strpos($_concept->native_field, 'CConstantesMedicales') !== false}}
              {{mb_include module=forms template=inc_ex_object_field_constantes_comment ex_object=$ex_object ex_field=$ex_field}}
            {{/if}}
          {{/if}}
        {{/if}}
      </td>
    </tr>
  </table>
{{else}}
  {{if $ex_field->_ref_hypertext_links && ($ex_class->pixel_positionning || ($ex_field->coord_label_x == null && $ex_field->coord_label_y == null))}}
    {{mb_include module=forms template=inc_vw_field_hypertext_links object=$ex_field}}
  {{/if}}

  {{if $ex_class->pixel_positionning}}
    {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
  {{/if}}

  {{$ex_field->prefix}}

  {{assign var=_size value=null}}
  {{if $ex_class->pixel_positionning}}
    {{assign var=_size value=''}}
  {{else}}
    {{assign var=_spec value=$ex_field->_ref_concept->_concept_spec}}
    {{if $_spec|instanceof:'Ox\Core\FieldSpecs\CFloatSpec'}}
      {{assign var=_size value=4}}
    {{/if}}
  {{/if}}

    {{mb_field
      object=$ex_object
      field=$_field_name
      register=true
      increment=true
      form=$form
      emptyLabel=$field_emptyLabel
      style=$_style
      defaultstyle=1
      readonly=$field_readonly
      tabindex=$ex_field->tab_index
      size=$_size
    }}

    {{mb_include module=forms template=inc_ex_field_link_formula ex_object=$ex_object ex_field=$ex_field spec=$_spec}}
  {{$ex_field->suffix}}

    {{if $conf.forms.CExConcept.native_field}}
      {{assign var=_concept value=$ex_field->_ref_concept}}

      {{if $_concept && $_concept->native_field}}
        {{if strpos($_concept->native_field, 'CConstantesMedicales') !== false}}
          {{mb_include module=forms template=inc_ex_object_field_constantes_comment ex_object=$ex_object ex_field=$ex_field}}
        {{/if}}
      {{/if}}
    {{/if}}
{{/if}}
