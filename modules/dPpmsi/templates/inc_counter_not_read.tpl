{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=context_id value=$context->_id}}
{{assign var=context_class value=$context->_class}}

{{if !$notReadFiles.$context_id|@count}}
  {{mb_return}}
{{/if}}

{{unique_id var=uid_unread}}

<span id="counter_{{$uid_unread}}" onmouseover="ObjectTooltip.createDOM(this, 'tooltip_file_{{$context->_id}}_{{$uid_unread}}')">
    {{$notReadFiles.$context_id|@count}}
</span>
<div style="display: none" id="tooltip_file_{{$context_id}}_{{$uid_unread}}">
    {{mb_include module=files template=inc_read_file object_id=$context_id object_class=$context_class documents=$notReadFiles.$context_id uid_unread=$uid_unread}}
</div>
