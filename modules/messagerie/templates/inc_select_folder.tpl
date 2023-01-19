{{*
 * @package Mediboard\messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<option value="{{$folder->_id}}" data-type="{{$type}}"{{if $subject && $subject->_id == $folder->_id}} disabled{{elseif $parent_id == $folder->_id}} selected{{/if}}>
  {{section name=indent loop=$iteration start=0}}
    &nbsp;&nbsp;
  {{/section}}
  {{$folder->name}}
</option>
{{if $folder->_ref_children|@count != 0 && $iteration < $iteration_max}}
  {{foreach from=$folder->_ref_children item=_folder}}
    {{math assign=iteration equation="x+1" x=$iteration}}
    {{mb_include module=messagerie template=inc_select_folder folder=$_folder}}
  {{/foreach}}
{{/if}}