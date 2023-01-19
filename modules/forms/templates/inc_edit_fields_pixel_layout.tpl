{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  Control.Tabs.create("field_groups_layout");
});

toggleList = function(select, ex_group_id) {
  // Quick search reset
  $$(".hostfield-list-"+ex_group_id).each(function(e) {
    e.select('li').invoke('show');
  });

  var input = $('forms-hostfields-quicksearch-'+ex_group_id);
  input.value = '';
  input.onkeyup();

  $$(".hostfield-list-"+ex_group_id).invoke("hide");
  $$(".hostfield-"+ex_group_id+"-"+$V(select))[0].show();
}
</script>

<ul class="control_tabs me-control-tabs-wraped" id="field_groups_layout" style="font-size: 0.9em;">
  {{foreach from=$ex_class->_ref_groups item=_group}}
    <li>
      <a href="#group-layout-{{$_group->_guid}}" style="padding: 2px 4px;">
        {{$_group->name}} <small>({{$_group->_ref_fields|@count}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

{{assign var=groups value=$ex_class->_ref_groups}}

<form name="form-grid-layout" method="post" onsubmit="return false" class="prepared pixel-positionning">
  
{{foreach from=$groups key=_group_id item=_group}}
<div id="group-layout-{{$_group->_guid}}" style="display: none;" class="group-layout" data-group_id="{{$_group->_id}}">
  <table class="main layout">
    <tr>
      <td class="narrow">
        <div style="height: 600px; /*overflow: auto;*/" class="scrollable">
          <div class="pixel-grid" unselectable>
            {{* PICTURES *}}
            {{foreach from=$_group->_ref_root_pictures item=_picture}}
              {{mb_include
                module=forms
                template=inc_ex_picture_draggable
                _picture=$_picture
              }}
            {{/foreach}}

            {{* SUBGROUPS *}}
            {{foreach from=$_group->_ref_subgroups item=_subgroup}}
              {{mb_include
                module=forms
                template=inc_ex_subgroup_draggable
                _subgroup=$_subgroup
              }}
            {{/foreach}}

            {{* FIELDS *}}
            {{foreach from=$_group->_ref_root_fields item=_field}}
              {{if !$_field->disabled && !$_field->hidden}}
                {{mb_include
                  module=forms
                  template=inc_ex_field_draggable_children
                  _field=$_field
                }}
              {{/if}}
            {{/foreach}}

            {{* MESSAGES *}}
            {{foreach from=$_group->_ref_root_messages item=_message}}
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
            {{foreach from=$_group->_ref_root_host_fields item=_host_field}}
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
            {{foreach from=$_group->_ref_root_action_buttons item=_action_button}}
              <div class="resizable {{if $_action_button->_no_size}} no-size {{/if}} action-button" tabIndex="0" data-action_button_id="{{$_action_button->_id}}"
                   style="left:{{$_action_button->coord_left}}px; top:{{$_action_button->coord_top}}px; width:{{$_action_button->coord_width}}px; height:{{$_action_button->coord_height}}px;">
                {{mb_include module=forms template=inc_resizable_handles}}
                {{mb_include module=forms template=inc_action_button_draggable_pixel action_button=$_action_button action=$_action_button->action icon=$_action_button->icon}}
              </div>
            {{/foreach}}

            {{* WIDGETS *}}
            {{foreach from=$_group->_ref_root_widgets item=_widget}}
              <div class="resizable {{if $_widget->_no_size}} no-size {{/if}} form-widget" tabIndex="0" data-ex_class_widget_id="{{$_widget->_id}}"
                   style="left:{{$_widget->coord_left}}px; top:{{$_widget->coord_top}}px; width:{{$_widget->coord_width}}px; height:{{$_widget->coord_height}}px;">
                {{mb_include module=forms template=inc_resizable_handles}}
                {{mb_include module=forms template=inc_widget_draggable_pixel widget=$_widget->getWidgetDefinition() ex_object=$object->_ex_object}}
              </div>
            {{/foreach}}
          </div>
        </div>
      </td>
      <td>
        <script>
          Main.add(function(){
            Control.Tabs.create("tab-other-form-items-{{$_group->_guid}}");
          });
        </script>
        
        <ul class="control_tabs small" id="tab-other-form-items-{{$_group->_guid}}">
          <li>
            <a href="#tab-host-fields-outofgrid-{{$_group->_guid}}">{{tr}}CExClassFieldGroup-back-host_fields{{/tr}}</a>
          </li>
          <li>
            <a href="#tab-action-buttons-{{$_group->_guid}}">{{tr}}CExClassFieldGroup-back-action_buttons{{/tr}}</a>
          </li>
          <li>
            <a href="#tab-widgets-{{$_group->_guid}}">{{tr}}CExClassFieldGroup-back-widgets{{/tr}}</a>
          </li>
        </ul>
        <div id="tab-host-fields-outofgrid-{{$_group->_guid}}" class="tab-host-fields">
          {{mb_include module=forms template=inc_outofgrid_hostfields layout_editor=true}}
        </div>
        <div id="tab-action-buttons-{{$_group->_guid}}" class="tab-action-buttons">
          {{mb_include module=forms template=inc_outofgrid_action_buttons layout_editor=true}}
        </div>
        <div id="tab-widgets-{{$_group->_guid}}" class="tab-widgets">
          {{mb_include module=forms template=inc_outofgrid_widgets layout_editor=true}}
        </div>
      </td>
    </tr>
  </table>
</div>
{{/foreach}}

</form>
