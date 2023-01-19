{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    UserMessageDestGroup.edit_form = getForm('editUserMessageDestGroup');
    var form = getForm('UserMessageDestGroup-addUser');
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('input_field', 'user_view')
      .autoComplete(form.elements['user_view'], null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          UserMessageDestGroup.addUser(selected.getAttribute("id").split("-")[2], selected.down('.view').innerHTML);
          $V(form.elements['user_view'], '');
        }
      });
  });
</script>

<div>
  <form name="editUserMessageDestGroup" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close.curry());">
    {{mb_class object=$recipient_group}}
    {{mb_key object=$recipient_group}}

    <input type="hidden" name="m" value="messagerie">
    <input type="hidden" name="dosql" value="do_aed_user_message_dest_group">
    {{if $recipient_group->_id}}
      <input type="hidden" name="del" value="0">
    {{/if}}

    {{mb_field object=$recipient_group field=group_id hidden=true}}
    <input type="hidden" name="added_users_id" value="">
    <input type="hidden" name="removed_links_id" value="">

    <table class="form">
      {{mb_include module=system template=inc_form_table_header object=$recipient_group}}
      <tr>
        <th>{{mb_label object=$recipient_group field=name}}</th>
        <td>{{mb_field object=$recipient_group field=name}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$recipient_group field=color}}</th>
        <td>{{mb_field object=$recipient_group field=color form=editUserMessageDestGroup}}</td>
      </tr>
    </table>
  </form>
</div>

<form name="UserMessageDestGroup-addUser" method="post" action="?" onsubmit="return false;">
  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}CUserMessageDestGroup-back-dest_groups{{/tr}}</th>
    </tr>
    <tr>
      <th><label for="UserMessageDestGroup-addUser-user_view">{{tr}}CUserMessageDestGroup-action-add_user{{/tr}}</label></th>
      <td>
        <input type="text" name="user_view" value="">
      </td>
    </tr>
  </table>
</form>

<div id="members-list" class="me-display-flex me-flex-wrap me-margin-left-3">
  {{foreach from=$recipient_group->_user_links item=link name=user_links}}
    <div id="{{$link->_guid}}" class="me-user-chips me-margin-right-3" onmouseover="ObjectTooltip.createEx(this, '{{$link->_user->_guid}}');">
      <div>
        <div class="me-user-chips-content">{{$link->_user}}</div>
        <button type="button" class="cancel me-tertiary notext" onclick="UserMessageDestGroup.removeUserLink('{{$link->_id}}');">{{tr}}Delete{{/tr}}</button>
      </div>
    </div>
  {{/foreach}}
</div>

<div style="text-align: center;">
  <button type="button" class="save" onclick="UserMessageDestGroup.edit_form.onsubmit();">{{tr}}Save{{/tr}}</button>
  {{if $recipient_group->_id}}
    <button type="button" class="trash" onclick="$V(UserMessageDestGroup.edit_form.elements['del'], '1'); UserMessageDestGroup.edit_form.onsubmit();">{{tr}}Delete{{/tr}}</button>
  {{/if}}
</div>