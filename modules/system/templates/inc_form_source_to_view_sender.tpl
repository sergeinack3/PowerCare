{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-{{$source_to_vw_sender->_guid}}" action="?m={{$m}}" method="post" onsubmit="return SourceToViewSender.onSubmit(this);">
  {{mb_class object=$source_to_vw_sender}}
  {{mb_key   object=$source_to_vw_sender}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include template=inc_form_table_header object=$source_to_vw_sender}}
    
    <tr>
      <th>{{mb_label object=$source_to_vw_sender field=sender_id}}</th>
      <td>{{mb_field object=$source_to_vw_sender field=sender_id form="Edit-`$source_to_vw_sender->_guid`" autocomplete="true,1,50,true,true"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$source_to_vw_sender field=source_id}}</th>
      <td>{{mb_field object=$source_to_vw_sender field=source_id form="Edit-`$source_to_vw_sender->_guid`" autocomplete="true,1,50,true,true"}}</td>
    </tr>
    
    <tr>
      <td class="button" colspan="2">
        {{if $source_to_vw_sender->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="SourceToViewSender.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
