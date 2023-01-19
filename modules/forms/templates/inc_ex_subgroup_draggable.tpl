{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="resizable subgroup" tabIndex="0" data-subgroup_id="{{$_subgroup->_id}}"
     style="left:{{$_subgroup->coord_left}}px; top:{{$_subgroup->coord_top}}px; width:{{$_subgroup->coord_width}}px; height:{{$_subgroup->coord_height}}px; "
     ondblclick="ExSubgroup.edit({{$_subgroup->_id}}); Event.stop(event);"
     onclick="this.focus(); Event.stop(event);"
     unselectable="on"
     onselectstart="return false;"
  >
{{mb_include module=forms template=inc_resizable_handles}}
  <div class="overlayed">
    <fieldset {{if !$_subgroup->title}} class="no-label" {{else}} class="with-label" {{/if}}>
      {{if $_subgroup->title}}
        <legend>{{$_subgroup->title}}</legend>
      {{/if}}

      <div style="position: relative;">
        {{* PICTURES *}}
        {{foreach from=$_subgroup->_ref_children_pictures item=_picture}}
          {{mb_include
          module=forms
          template=inc_ex_picture_draggable
          _picture=$_picture
          }}
        {{/foreach}}

        {{* SUBGROUPS *}}
        {{foreach from=$_subgroup->_ref_children_groups item=_sub_subgroup}}
          {{mb_include
          module=forms
          template=inc_ex_subgroup_draggable
          _subgroup=$_sub_subgroup
          }}
        {{/foreach}}

        {{* FIELDS *}}
        {{foreach from=$_subgroup->_ref_children_fields item=_field}}
          {{if !$_field->disabled && !$_field->hidden}}
            {{mb_include
            module=forms
            template=inc_ex_field_draggable_children
            _field=$_field
            }}
          {{/if}}
        {{/foreach}}

        {{* MESSAGES *}}
        {{foreach from=$_subgroup->_ref_children_messages item=_message}}
          <div class="resizable {{if $_message->_no_size}} no-size {{/if}} draggable-message" tabIndex="0" data-message_id="{{$_message->_id}}"
               style="left:{{$_message->coord_left}}px; top:{{$_message->coord_top}}px; width:{{$_message->coord_width}}px; height:{{$_message->coord_height}}px;">
            {{mb_include module=forms template=inc_resizable_handles}}
            {{mb_include
            module=forms
            template=inc_ex_message_draggable
            _field=$_message
            ex_group_id=$_group_id
            _type=""
            }}
          </div>
        {{/foreach}}

        {{* HOST FIELDS *}}
        {{foreach from=$_subgroup->_ref_children_host_fields item=_host_field}}
          {{if $_host_field->type}}
            <div class="resizable {{if $_host_field->_no_size}} no-size {{/if}} host-field" tabIndex="0" data-host_field_id="{{$_host_field->_id}}"
                 style="left:{{$_host_field->coord_left}}px; top:{{$_host_field->coord_top}}px; width:{{$_host_field->coord_width}}px; height:{{$_host_field->coord_height}}px;">
              {{mb_include module=forms template=inc_resizable_handles}}
              {{assign var=_host_class value=$_host_field->host_class}}

              {{mb_include module=forms template=inc_ex_host_field_draggable
              _host_field=$_host_field
              ex_group_id=$_group_id
              _field=$_host_field->_field
              trad=true
              _type=$_host_field->type
              _class=$_host_class}}
            </div>
          {{/if}}
        {{/foreach}}

        {{* ACTION BUTTONS *}}
        {{foreach from=$_subgroup->_ref_children_action_buttons item=_action_button}}
          <div class="resizable {{if $_action_button->_no_size}} no-size {{/if}} action-button" tabIndex="0" data-action_button_id="{{$_action_button->_id}}"
               style="left:{{$_action_button->coord_left}}px; top:{{$_action_button->coord_top}}px; width:{{$_action_button->coord_width}}px; height:{{$_action_button->coord_height}}px;">
            {{mb_include module=forms template=inc_resizable_handles}}
            {{mb_include module=forms template=inc_action_button_draggable_pixel action_button=$_action_button action=$_action_button->action icon=$_action_button->icon}}
          </div>
        {{/foreach}}

        {{* WIDGETS *}}
        {{foreach from=$_subgroup->_ref_children_widgets item=_widget}}
          <div class="resizable {{if $_widget->_no_size}} no-size {{/if}} form-widget" tabIndex="0" data-ex_class_widget_id="{{$_widget->_id}}"
               style="left:{{$_widget->coord_left}}px; top:{{$_widget->coord_top}}px; width:{{$_widget->coord_width}}px; height:{{$_widget->coord_height}}px;">
            {{mb_include module=forms template=inc_resizable_handles}}
            {{mb_include module=forms template=inc_widget_draggable_pixel widget=$_widget->getWidgetDefinition() ex_object=$object->_ex_object}}
          </div>
        {{/foreach}}
      </div>
    </fieldset>
  </div>
</div>
