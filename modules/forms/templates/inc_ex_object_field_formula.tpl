{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ex_form_hash value=false}}

{{if $show_label}}
  <table class="main layout">
    <tr>
      <td class="narrow input-label">{{mb_label object=$ex_object field=$_field_name}}</td>
      <td class="narrow">
        {{if $ex_class->pixel_positionning}}
          {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
        {{/if}}
      </td>
      <td style="white-space: nowrap;">
        {{$ex_field->prefix}}
        {{mb_field
          object=$ex_object
          field=$_field_name
          readonly=true
          style="$_style"
          class="noresize formula-result"
          rows=5
          title=$ex_field->_formula
          defaultstyle=1
        }}
        {{$ex_field->suffix}}
      </td>
      <td class="narrow">
        {{if $ex_form_hash}}
          <button type="button" class="cancel notext" style="margin-left: -1px;" onclick="$V($('editExObject_{{$ex_form_hash}}_{{$_field_name}}'),'')">
        {{else}}
          <button type="button" class="cancel notext" style="margin-left: -1px;" onclick="$V($(this).previous(),'')">
        {{/if}}
          Vider
        </button>
      </td>
    </tr>
  </table>
{{else}}
  <div style="white-space: nowrap;">
    {{if $ex_class->pixel_positionning}}
      {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$ex_field}}
    {{/if}}
    {{$ex_field->prefix}}
    {{mb_field
      object=$ex_object
      field=$_field_name
      readonly=true
      style="$_style"
      class="noresize formula-result"
      rows=5
      title=$ex_field->_formula
      defaultstyle=1
    }}
    {{$ex_field->suffix}}
    {{if $ex_form_hash}}
      <button type="button" class="cancel notext" style="margin-left: -1px;" onclick="$V($('editExObject_{{$ex_form_hash}}_{{$_field_name}}'),'')">
    {{else}}
      <button type="button" class="cancel notext" style="margin-left: -1px;" onclick="$V($(this).previous(),'')">
    {{/if}}
      Vider
    </button>
  </div>
{{/if}}