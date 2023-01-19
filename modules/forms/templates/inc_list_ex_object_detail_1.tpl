{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$ex_objects item=_ex_objects key=_ex_class_id}}
  {{assign var=_ex_class value=$ex_classes.$_ex_class_id}}
  
  {{if $_ex_objects|@count}}
  {{if !$ex_class_id}}
    <h3 style="margin: 0.5em 1em;">{{$_ex_class->name}}</h3>
  {{/if}}
  
  {{foreach from=$_ex_objects item=_ex_object name=_ex_object}}
    <table class="layout">
      <tr>
        <td>
          {{assign var=_can_view value=$_ex_object->canPerm('v')}}

          <button class="edit notext compact me-tertiary" {{if !$_ex_object->canPerm('e')}} disabled {{/if}}
                  onclick="ExObject.edit('{{$_ex_object->_id}}', '{{$_ex_object->_ex_class_id}}', '{{$_ex_object->_ref_object->_guid}}', '@ExObject.refreshSelf.{{$self_guid}}')">
            {{tr}}Edit{{/tr}}
          </button>

          {{assign var=classes value="search notext compact"}}
            {{if !$_can_view}}
              {{assign var=classes value="`$classes` disabled"}}
          {{/if}}
          {{me_button label="Display" icon=$classes onclick="ExObject.display('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`', '`$_ex_object->_ref_object->_guid`')"}}

          {{assign var=classes value="history notext compact"}}
          {{if !$_can_view}}
              {{assign var=classes value="`$classes` disabled"}}
          {{/if}}
          {{me_button label="History" icon=$classes onclick="ExObject.history('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`')"}}

          {{assign var=classes value="print notext compact"}}
          {{if !$_can_view}}
              {{assign var=classes value="`$classes` disabled"}}
          {{/if}}
          {{me_button label="Print" icon=$classes onclick="ExObject.print('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`', '`$_ex_object->_ref_object->_guid`')"}}


            {{me_dropdown_button button_label=Actions button_icon="opt" button_class="notext me-tertiary"
            container_class="me-dropdown-button-left me-dropdown-button-top me-float-left"}}

          {{mb_include module=forms template=inc_ex_object_verified_icon ex_object=$_ex_object}}
          
          {{if array_key_exists($reference_id,$alerts) && array_key_exists($_ex_class_id,$alerts.$reference_id)}}
            <span style="color: red; float: right;">
              {{foreach from=$alerts.$reference_id.$_ex_class_id item=_alert}}
                {{if $_alert.ex_object->_id == $_ex_object->_id}}
                  <span style="padding: 0 4px;" title="{{tr}}CExObject_{{$_alert.ex_class->_id}}-{{$_alert.ex_class_field->name}}{{/tr}}: {{$_alert.result}}">
                    {{mb_include module=forms template=inc_ex_field_threshold threshold=$_alert.alert title="none"}}
                  </span>
                {{/if}}
              {{/foreach}}
            </span>
          {{/if}}
        </td>
        <td class="text compact">
          <strong class="me-color-black-high-emphasis" style="color: #000;">{{mb_value object=$_ex_object field=datetime_create}}</strong>
          <br />
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_ex_object->_ref_object->_guid}}')">
            {{$_ex_object->_ref_object}}
          </span>
          {{if $_ex_object->additional_id}}
            <br />
            <span style="color: #AA0000" onmouseover="ObjectTooltip.createEx(this, '{{$_ex_object->_ref_additional_object->_guid}}')">
              {{$_ex_object->_ref_additional_object}}
            </span>
          {{/if}}
        </td>
      </tr>
    </table>
  {{/foreach}}
    
  <hr class="me-no-display" style="border-color: #aaa;" />
  {{/if}}
    
{{foreachelse}}
  <div class="empty">{{tr}}CExClass.none{{/tr}}</div>
{{/foreach}}