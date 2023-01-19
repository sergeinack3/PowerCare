{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $objects|@count}}
  {{if $insertion == "after"}}
    <tbody class="object-list">
  {{/if}}
  
    {{foreach from=$objects item=_object}}
    <tr>
      <td style="padding-left: 18px;" class="text">
        {{if $tag->color}}
          <span style="float: right; border-right: 1em solid #{{$tag->color}};" title="{{$tag}}">&nbsp;</span>
        {{/if}}
        
        {{foreach from=$_object->_ref_tag_items item=_tag_item}}
          {{if $_tag_item->_ref_tag->color}}
          <span style="float: right; border-right: 0.8em solid #{{$_tag_item->_ref_tag->color}}; margin-left: -3px;" title="{{$_tag_item->_ref_tag}}">&nbsp;</span>
          {{/if}}
        {{/foreach}}
        
        <a href="#edit.{{$_object->_guid}}" onclick="MbObject.edit(this)" data-object_guid="{{$_object->_guid}}">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}');">
            {{$_object}}
          </span>
        </a>
      </td>
      {{foreach from=$columns item=_column}}
        <td>
          {{mb_value object=$_object field=$_column}}
        </td>
      {{/foreach}}
    </tr>
    {{foreachelse}}
      {{if $count_children == 0}}
      <tr>
        {{math assign=colspan equation="x+1" x=$columns|@count}}
        
        <td class="empty" colspan="{{$colspan}}">
          <div style="{{if $tag->color}}border-right: 1em solid #{{$tag->color}};{{/if}}">
            {{tr}}{{$tag->object_class}}.none{{/tr}}
          </div>
        </td>
      </tr>
      {{/if}}
    {{/foreach}}
    
  {{if $insertion == "after"}}
    </tbody>
  {{/if}}
{{/if}}