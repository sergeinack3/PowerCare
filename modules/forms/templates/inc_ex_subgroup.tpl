{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_properties value=$_subgroup->_default_properties}}

{{assign var=_style value=""}}
{{foreach from=$_properties key=_type item=_value}}
  {{if $_value != ""}}
    {{assign var=_style value="$_style $_type:$_value;"}}
  {{/if}}
{{/foreach}}

<div class="resizable subgroup group-layout" id="subgroup-{{$_subgroup->_guid}}"
     style="left:{{$_subgroup->coord_left}}px; top:{{$_subgroup->coord_top}}px; width:{{$_subgroup->coord_width}}px; height:{{$_subgroup->coord_height}}px;">
  <fieldset {{if !$_subgroup->title}} class="no-label" {{else}} class="with-label" {{/if}} style="{{$_style}}">
    {{if $_subgroup->title}}
      <legend>{{$_subgroup->title}}</legend>
    {{/if}}

    <div style="position: relative;">
      {{* ----- PICTURES ----- *}}
      {{foreach from=$_subgroup->_ref_children_pictures item=_picture}}
        {{if !$_picture->disabled && $_picture->_ref_file && $_picture->_ref_file->_id}}
          {{mb_include module=forms template=inc_ex_picture}}
        {{/if}}
      {{/foreach}}

      {{* ----- SUBGROUPS ----- *}}
      {{foreach from=$_subgroup->_ref_children_groups item=_sub_subgroup}}
        {{mb_include
        module=forms
        template=inc_ex_subgroup
        _subgroup=$_sub_subgroup
        }}
      {{/foreach}}

      {{* ----- FIELDS ----- *}}
      {{foreach from=$_subgroup->_ref_children_fields item=_field}}
        {{if !$_field->disabled}}
          {{assign var=_field_name value=$_field->name}}

          {{if $_field->hidden}}
            {{mb_field object=$ex_object field=$_field_name hidden=true}}
          {{else}}
            {{assign var=_style value=""}}
              {{assign var=_properties value=$_field->_default_properties}}

              {{foreach from=$_properties key=_type item=_value}}
                {{if $_value != ""}}
                  {{assign var=_style value="$_style $_type:$_value;"}}
                {{/if}}
              {{/foreach}}

            {{assign var=_spec value=$ex_object->_specs.$_field_name}}
            {{assign var=overflow value=''}}
            {{if ($readonly || $_field->readonly) && $_spec|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}}
              {{assign var=overflow value="overflow-x: hidden; overflow-y: auto;"}}
            {{/if}}

            <div class="field-{{$_field_name}} resizable field-input {{if $_field->_no_size}} no-size {{/if}}"
                 style="left:{{$_field->coord_left}}px; top:{{$_field->coord_top}}px; width:{{$_field->coord_width}}px; height:{{$_field->coord_height}}px; {{$overflow}}; {{$_style}}" defaultstyle="1">
              {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field form="editExObject_$ex_form_hash"}}
            </div>
          {{/if}}
        {{/if}}
      {{/foreach}}

      {{* ----- MESSAGES ----- *}}
      {{foreach from=$_subgroup->_ref_children_messages item=_message}}
        <div class="resizable {{if $_message->_no_size}} no-size {{/if}}" id="message-{{$_message->_guid}}"
             style="left:{{$_message->coord_left}}px; top:{{$_message->coord_top}}px; width:{{$_message->coord_width}}px; height:{{$_message->coord_height}}px; {{if !$_message->description}}pointer-events: none;{{/if}}">
          {{mb_include module=forms template=inc_ex_message}}
        </div>
      {{/foreach}}

      {{* ----- HOST FIELDS ----- *}}
      {{foreach from=$_subgroup->_ref_children_host_fields item=_host_field}}
        {{if $_host_field->type}}
          <div class="resizable {{if $_host_field->_no_size}} no-size {{/if}}" data-host_field_id="{{$_host_field->_id}}"
               style="left:{{$_host_field->coord_left}}px; top:{{$_host_field->coord_top}}px; width:{{$_host_field->coord_width}}px; height:{{$_host_field->coord_height}}px;">
            {{assign var=_host_class value=$_host_field->host_class}}

            {{if $_host_field->type == "label"}}
              {{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}
            {{else}}
              {{if $_host_field->_ref_host_object->_id}}
                {{mb_value object=$_host_field->_ref_host_object field=$_host_field->_field}}
              {{else}}
                <div class="info empty opacity-30">Information non disponible</div>
              {{/if}}
            {{/if}}
          </div>
        {{/if}}
      {{/foreach}}

      {{* ACTION BUTTONS *}}
      {{foreach from=$_subgroup->_ref_children_action_buttons item=_action_button}}
        <div class="resizable {{if $_action_button->_no_size}} no-size {{/if}} action-button"
             data-action_button_id="{{$_action_button->_id}}"
             id="action_button-{{$_action_button->_guid}}"
             style="left:{{$_action_button->coord_left}}px; top:{{$_action_button->coord_top}}px; width:{{$_action_button->coord_width}}px; height:{{$_action_button->coord_height}}px;">
          {{mb_include module=forms template=inc_action_button action_button=$_action_button}}
        </div>
      {{/foreach}}
    </div>
  </fieldset>
</div>
