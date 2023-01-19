{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$conf.forms.CExClass.pixel_layout_delimiter}}
  <style>
    .pixel-grid {
      width: auto;
      height: auto;
      border: 0 !important;
      background: #f9f9f9;
      padding: 0;
    }
  </style>
{{/if}}

{{if !$readonly}}
  {{mb_script module=forms script=ex_class_layout_editor_pixel}}

  <script>
    Main.add(function(){
      window.currentPictures = {};
      var startDrag = function(draggable) {
        var box = draggable.element;
        var coords = ExClass.getBoxCoords(box);
        var currentData = window.currentPictures[box.get("picture_id")];
        currentData.coord_top    = coords.coord_top    || currentData.coord_top;
        currentData.coord_left   = coords.coord_left   || currentData.coord_left;
        currentData.coord_width  = coords.coord_width  || currentData.coord_width;
        currentData.coord_height = coords.coord_height || currentData.coord_height;
        currentData.coord_angle  = coords.coord_angle  || currentData.coord_angle;
      };
      var endDrag = function(draggable) {
        var box = draggable.element;
        var coords = ExClass.getBoxCoords(box);
        var currentData = window.currentPictures[box.get("picture_id")];
        currentData.coord_top    = coords.coord_top    || currentData.coord_top;
        currentData.coord_left   = coords.coord_left   || currentData.coord_left;
        currentData.coord_width  = coords.coord_width  || currentData.coord_width;
        currentData.coord_height = coords.coord_height || currentData.coord_height;
        currentData.coord_angle  = coords.coord_angle  || currentData.coord_angle;
      };
      ExClass.initPixelLayoutEditor(startDrag, endDrag, ".resizable.form-picture");
    });
  </script>

  <div class="pixel-positionning">
  {{foreach from=$groups item=_group}}
    <div id="tab-{{$_group->_guid}}" style="display: none; position: relative;" class="pixel-grid group-layout">
      {{* ----- PICTURES ----- *}}
      {{foreach from=$_group->_ref_root_pictures item=_picture}}
        {{if !$_picture->disabled && ($_picture->_ref_file && $_picture->_ref_file->_id || $_picture->drawable)}}
          {{mb_include module=forms template=inc_ex_picture}}
        {{/if}}
      {{/foreach}}

      {{* ----- SUB GROUPS ----- *}}
      {{foreach from=$_group->_ref_subgroups item=_subgroup}}
        {{mb_include
          module=forms
          template=inc_ex_subgroup
          _subgroup=$_subgroup
        }}
      {{/foreach}}

      {{* ----- FIELDS ----- *}}
      {{foreach from=$_group->_ref_root_fields item=_field}}
        {{if !$_field->disabled}}
          {{assign var=_field_name value=$_field->name}}

          {{if $_field->hidden}}
            <div class="field-{{$_field->name}}">
              {{mb_field object=$ex_object field=$_field_name hidden=true}}
            </div>
          {{else}}
            {{assign var=_style value=""}}
              {{assign var=_properties value=$_field->_default_properties}}

              {{foreach from=$_properties key=_type item=_value}}
                {{if $_value != ""}}
                  {{assign var=_style value="$_style $_type:$_value;"}}
                {{/if}}
              {{/foreach}}

            <div class="resizable field-{{$_field_name}} field-input {{if $_field->_no_size}} no-size {{/if}}"
                 style="left:{{$_field->coord_left}}px; top:{{$_field->coord_top}}px; width:{{$_field->coord_width}}px; height:{{$_field->coord_height}}px; {{$_style}}" defaultstyle="1">
              {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field form="editExObject_$ex_form_hash"}}
            </div>
          {{/if}}
        {{/if}}
      {{/foreach}}

      {{* ----- MESSAGES ----- *}}
      {{foreach from=$_group->_ref_root_messages item=_message}}
        <div class="resizable {{if $_message->_no_size}} no-size {{/if}} draggable-message" id="message-{{$_message->_guid}}"
             style="left:{{$_message->coord_left}}px; top:{{$_message->coord_top}}px; width:{{$_message->coord_width}}px; height:{{$_message->coord_height}}px;">

          {{mb_include module=forms template=inc_ex_message ex_class=$ex_object->_ref_ex_class}}
        </div>
      {{/foreach}}

      {{* ----- HOST FIELDS ----- *}}
      {{foreach from=$_group->_ref_root_host_fields item=_host_field}}
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
      {{foreach from=$_group->_ref_root_action_buttons item=_action_button}}
        <div class="resizable {{if $_action_button->_no_size}} no-size {{/if}} action-button"
             id="action_button-{{$_action_button->_guid}}"
             data-action_button_id="{{$_action_button->_id}}"
             style="left:{{$_action_button->coord_left}}px; top:{{$_action_button->coord_top}}px; width:{{$_action_button->coord_width}}px; height:{{$_action_button->coord_height}}px;">
          {{mb_include module=forms template=inc_action_button action_button=$_action_button}}
        </div>
      {{/foreach}}
      
      {{* WIDGETS ARE OUTSIDE OF <form> BECAUSE THEY MAY CONTAIN ONE *}}
    </div>
  {{/foreach}}
  </div>
{{else}}

  {{foreach from=$groups item=_group}}
    <div class="pixel-grid-page">
      <h3 style="border-bottom: 1px solid #999;">{{$_group->name}}</h3>
  
      <div id="tab-{{$_group->_guid}}" style="position: relative;" class="pixel-positionning pixel-grid-print">
        {{* ----- PICTURES ----- *}}
        {{foreach from=$_group->_ref_root_pictures item=_picture}}
          {{if !$_picture->disabled && ($_picture->_ref_file && $_picture->_ref_file->_id || $_picture->drawable)}}
            {{mb_include module=forms template=inc_ex_picture}}
          {{/if}}
        {{/foreach}}
  
        {{* ----- SUB GROUPS ----- *}}
        {{foreach from=$_group->_ref_subgroups item=_subgroup}}
          {{mb_include
          module=forms
          template=inc_ex_subgroup
          _subgroup=$_subgroup
          }}
        {{/foreach}}
  
        {{* ----- FIELDS ----- *}}
        {{foreach from=$_group->_ref_root_fields item=_field}}
          {{if !$_field->disabled && !$_field->hidden}}
            {{assign var=_field_name value=$_field->name}}

            {{assign var=_style value=""}}
              {{assign var=_properties value=$_field->_default_properties}}

              {{foreach from=$_properties key=_type item=_value}}
                {{if $_value != ""}}
                  {{assign var=_style value="$_style $_type:$_value;"}}
                {{/if}}
              {{/foreach}}

            <div class="resizable field-{{$_field_name}} field-input {{if $_field->_no_size}} no-size {{/if}}"
                 style="left:{{$_field->coord_left}}px; top:{{$_field->coord_top}}px; width:{{$_field->coord_width}}px; height:{{$_field->coord_height}}px; white-space: normal; overflow: auto; {{$_style}}" defaultstyle="1">
              {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field form="editExObject_$ex_form_hash"}}
            </div>
          {{/if}}
        {{/foreach}}
  
        {{* ----- MESSAGES ----- *}}
        {{foreach from=$_group->_ref_root_messages item=_message}}
          <div class="resizable {{if $_message->_no_size}} no-size {{/if}} draggable-message" id="message-{{$_message->_guid}}"
               style="left:{{$_message->coord_left}}px; top:{{$_message->coord_top}}px; width:{{$_message->coord_width}}px; height:{{$_message->coord_height}}px;">
            {{mb_include module=forms template=inc_ex_message ex_class=$ex_object->_ref_ex_class}}
          </div>
        {{/foreach}}
  
        {{* ----- HOST FIELDS ----- *}}
        {{foreach from=$_group->_ref_root_host_fields item=_host_field}}
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
      </div>
    </div>
  {{/foreach}}

{{/if}}
