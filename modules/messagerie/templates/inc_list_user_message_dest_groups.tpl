{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <td colspan="4">
      {{mb_include module=system template=inc_pagination total=$total step=25 current=$offset change_page='UserMessageDestGroup.changePage'}}
    </td>
  </tr>
  <tr>
    <th colspan="2">{{mb_title class=CUserMessageDestGroup field=name}}</th>
    <th>{{tr}}CUserMessageDestGroup-title-users{{/tr}}</th>
  </tr>
  {{foreach from=$recipient_groups item=recipient_group}}
    <tr>
      <td class="narrow">
        <button type="button" class="edit notext" onclick="UserMessageDestGroup.edit('{{$recipient_group->_id}}');">{{tr}}Edit{{/tr}}</button>
      </td>
      <td class="narrow">
        <div{{if $recipient_group->color}} class="me-padding-left-3" style="border-left: 3px solid #{{$recipient_group->color}};"{{/if}}>
          {{$recipient_group}}
        </div>
      </td>
      {{if count($recipient_group->_user_links)}}
        <td class="me-text-align-right">
          <div id="{{$recipient_group->_guid}}-users-list" class="me-display-flex me-flex-wrap">
            {{assign var=users_number value=$recipient_group->_user_links|@count}}
            {{foreach from=$recipient_group->_user_links item=user_link name=recipient_group_users}}
              <span class="recipient_group-users-list"{{if $smarty.foreach.recipient_group_users.iteration > 7}} style="display: none;"{{/if}}>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user_link->_user}}
              </span>
            {{/foreach}}
            {{if $users_number > 7}}
              {{math assign=hidden_users equation="x-7" x=$users_number}}
              <button type="button" class="right me-tertiary" onclick="UserMessageDestGroup.toggleUsersList('{{$recipient_group->_guid}}', this);">+{{$hidden_users}} {{tr}}CUserMessageDestGroup-_users|pl{{/tr}}</button>
            {{/if}}
          </div>
        </td>
      {{else}}
        <td class="empty">{{tr}}CUserMessageDestGroup-back-dest_groups.empty{{/tr}}</td>
      {{/if}}
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">{{tr}}CUserMessageDestGroup.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  {{if $total > 25}}
    <tr>
      <td colspan="4">
        {{mb_include module=system template=inc_pagination total=$total step=25 current=$offset change_page='UserMessageDestGroup.changePage'}}
      </td>
    </tr>
  {{/if}}
</table>