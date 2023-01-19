{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Filter" action="?" method="get">

<input type="hidden" name="m" value="{{$m}}" />
    
<table class="form">
  <tr>
    <th>{{mb_label object=$filter field=_status}}</th>
    <td>{{mb_field object=$filter field=_status emptyLabel=All}}</td>
  </tr>
</table>

</form>

<table class="tbl">

<tr>
  <th colspan="10" class="title">
    {{$messages|@count}}
    {{tr}}CMessage{{/tr}}s
    {{tr}}found{{/tr}}
  </th>
</tr>

<tr>
  <th colspan="2" class="narrow">{{mb_title class=CMessage field=titre}}</th>
  <th class="narrow">
    {{mb_label class=CMessage field=group_id}} <br />
    {{mb_label class=CMessage field=module_id}}
  </th>
  <th class="narrow">
    {{mb_title class=CMessage field=deb}} <br />
    {{mb_title class=CMessage field=fin}}
  </th>
  <th>
    {{mb_title class=CMessage field=corps}}
  </th>
</tr>

{{foreach from=$messages item=_message}}
<tbody class="hoverable">

<tr>
  <td>
    <button class="edit notext" onclick="Message.edit('{{$_message->_id}}');">
      {{tr}}Edit{{/tr}}
    </button> 
  </td>
  <td>
    {{mb_include module=system template=inc_object_notes object=$_message float=right}}

    {{assign var=class value=info}}
    {{if $_message->urgence == "urgent"}}{{assign var=class value=warning}}{{/if}}
    <div class="{{$class}} noted">
      <strong>{{mb_value object=$_message field=titre}}</strong>
    </div>
  </td>
  
  <td>
    {{if $_message->group_id}}
      {{$_message->_ref_group}}
    {{else}}
      {{tr}}All{{/tr}}
    {{/if}}
    <br />
    {{if $_message->module_id}}
      {{$_message->_ref_module_object}}
    {{else}}
      {{tr}}All{{/tr}}
    {{/if}}
  </td>
  
  <td>
    {{mb_value object=$_message field=deb}} <br />
    {{mb_value object=$_message field=fin}}
  </td>
  
  <td class="text compact">
    {{mb_value object=$_message field=corps}}
  </td>
</tr>

</tbody>
{{foreachelse}}
<tr>
  <td class="empty" colspan="5">{{tr}}CMessage.none{{/tr}}
</td>
</tr>
{{/foreach}}
  
</table>
