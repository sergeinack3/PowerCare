{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
{{foreach from=$categories item=_category key=_category_id}}
  {{foreach from=$_category->_ref_ex_classes item=ex_class_event name=ex_class_events}}
    {{mb_default var=quick_access_object value=null}}
  
    {{assign var=quick_access_possible value=true}}
    {{if $ex_class_event->_quick_access && (!$ex_class_event->_ref_constraint_object || !$ex_class_event->_ref_constraint_object->_id)}}
      {{assign var=quick_access_possible value=false}}
    {{/if}}
  
    {{assign var=ex_class value=$ex_class_event->_ref_ex_class}}
  
    <li data-ex_class_id="{{$ex_class_event->ex_class_id}}"
      {{if !$quick_access_possible}} disabled {{/if}}
      {{if $ex_class_event->_ref_constraint_object && $ex_class_event->_ref_constraint_object->_id}}
        data-reference_class="{{$ex_class_event->_ref_constraint_object->_class}}"
        data-reference_id="{{$ex_class_event->_ref_constraint_object->_id}}"
        data-quick_access_creation="{{$ex_class_event->_quick_access_creation}}"
      {{else}}
        data-reference_class="{{$reference_class}}"
        data-reference_id="{{$reference_id}}"
      {{/if}}
      
      data-host_class="{{$ex_class_event->host_class}}"
      data-event_name="{{$ex_class_event->event_name}}"
  
      style="padding: 0; border-left: 4px solid {{if $_category->_id}}#{{$_category->color}}{{else}}transparent{{/if}};
      {{if $_category->_id && $smarty.foreach.ex_class_events.first}} border-top: 1px solid #999;{{/if}}"
    >
      {{if $_category->_id && $smarty.foreach.ex_class_events.first}}
        <div style="font-weight: bold; background: #bbb; padding: 2px;">{{$_category->title}}</div>
      {{/if}}
      
      <div style="padding: 2px">
        {{if $ex_class_event->_quick_access}}
          &#9889;
          {{if !$quick_access_possible}}
            [Déclench. impossible]
          {{/if}}
        {{/if}}

        {{$ex_class_event->_ref_ex_class}}
      </div>
    </li>
  {{/foreach}}
{{/foreach}}
</ul>