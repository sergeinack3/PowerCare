{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form_uid}}
{{mb_default var=main_button_color value=null}}
{{mb_default var=alternativeButton value=null}}
{{if $form_name && $ex_objects|@count == 0}}
  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    if (ExObject.alternativeButton) {
      $('icon-{{$form_uid}}').show();
      $('button-{{$form_uid}}').hide();
    }
    else {
      $('icon-{{$form_uid}}').hide();
      $('button-{{$form_uid}}').show();
    }
  });
</script>

{{if !$form_name}}
    <button id="button-{{$form_uid}}" type="button" class="me-tertiary" style="display: none;" {{if $ex_objects|@count == 0}}disabled{{/if}} onclick="ObjectTooltip.createDOM(this, $(this).next().next(), {duration: 0});">
      <i class="fa fa-list form-icon" {{if $object->_class == 'CSejour' || $object->_class == 'COperation'}}style="color: {{$object->_completeness_color_form}}"{{/if}}></i> Form. ({{$count_available}})
    </button>
    <i id="icon-{{$form_uid}}"
       class="fa fa-list event-icon small_ambu small_pointer" style="display: none;background-color: {{if $main_button_color}}{{$main_button_color}}{{elseif (($object->_class == 'CSejour' || $object->_class == 'COperation') && $object->_completeness_color_form)}}{{$object->_completeness_color_form}}{{else}}grey{{/if}};"
       onclick="ObjectTooltip.createDOM(this, $(this).next(), {duration: 0});"
       title="{{tr}}module-ambu-Checklist form{{/tr}}"></i>
{{else}}
  <fieldset>
    <legend>Formulaire {{tr}}{{$object->_class}}-event-{{$event_name}}{{/tr}}</legend>
{{/if}}

<table class="layout" style="{{if !$form_name}} border: 1px solid #000; display: none; {{/if}} width: 400px; max-width: 700px;">
  {{foreach from=$ex_objects key=_ex_class_id item=_ex_objects}}
    <tr>
      <td style="text-align: right; {{if !$form_name}} font-weight: bold; vertical-align: middle; white-space: normal; min-width: 200px; {{/if}}">
        {{$ex_classes.$_ex_class_id->name}}
      </td>
      <td style="text-align: left; white-space: normal;">
        {{foreach from=$_ex_objects item=_ex_object}}
          {{if $_ex_object->_id}}
            <br />
            <button type="button" class="search notext" title="Voir le formulaire"
              {{if !$ex_classes.$_ex_class_id->canPerm("e")}} disabled {{/if}}
                    onclick="showExClassForm({{$_ex_class_id}}, '{{$object->_guid}}', '{{$_ex_object}}', '{{$_ex_object->_id}}', '{{$event_name}}', '{{$_element_id}}', null, null, null, null, null, null, '1')">
            </button>
            <button type="button" class="edit" title="{{mb_value object=$_ex_object field=owner_id}}"
              {{if !$ex_classes.$_ex_class_id->canPerm("e")}} disabled {{/if}}
              onclick="showExClassForm({{$_ex_class_id}}, '{{$object->_guid}}', '{{$_ex_object}}', '{{$_ex_object->_id}}', '{{$event_name}}', '{{$_element_id}}')">
              {{mb_value object=$_ex_object field=datetime_create}}

              {{if $ex_classes.$_ex_class_id->_formula_field}}
                {{assign var=_formula_field value=$ex_classes.$_ex_class_id->_formula_field}}

                <strong>= {{$_ex_object->$_formula_field}}</strong>
              {{/if}}
            </button>
          {{else}}
            <button type="button" class="new" value="{{$_ex_class_id}}"
                    {{if !$ex_classes.$_ex_class_id->canPerm("c")}} disabled {{/if}}
                    onclick="selectExClass(this, '{{$object->_guid}}', '{{$event_name}}', '{{$_element_id}}'{{if $form_name}}, '{{$form_name}}'{{/if}})">
              Nouveau
            </button>
          {{/if}}
        {{/foreach}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">{{tr}}CExClass.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{if $form_name}}
  </fieldset>
{{/if}}
