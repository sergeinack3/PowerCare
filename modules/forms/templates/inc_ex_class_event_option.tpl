{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=quick_access_object value=null}}

{{assign var=quick_access_possible value=true}}
{{if $ex_class_event->_quick_access && (!$ex_class_event->_ref_constraint_object || !$ex_class_event->_ref_constraint_object->_id)}}
  {{assign var=quick_access_possible value=false}}
{{/if}}

<option value="{{$ex_class_event->ex_class_id}}"
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
>
  {{if $ex_class_event->_quick_access}}
    &#9889;
    {{if !$quick_access_possible}}
      [Déclench. impossible]
    {{/if}}
  {{/if}}
  {{$view}}
</option>