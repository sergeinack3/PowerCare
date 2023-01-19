{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    UserMessage.refreshCounts();
  });
</script>

<div id="user_messages_actions" style="margin: 5px;">
  {{mb_include module=messagerie template=inc_usermessages_actions}}
</div>

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow">{{tr}}Date{{/tr}}</th>
    <th class="narrow">
      {{if $mode == "draft" || $mode == "sentbox"}}
        {{tr}}CUserMessageDest-to_user_id{{/tr}}
      {{else}}
        {{tr}}CUserMessageDest-from_user_id{{/tr}}
      {{/if}}
    </th>
    <th>{{tr}}CUserMessage-subject{{/tr}}</th>
    <th>{{tr}}CUserMessage-_abstract{{/tr}}</th>
  </tr>
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination total=$total current=$page change_page="UserMessage.refreshListPage" step=$app->user_prefs.nbMailList}}
    </td>
  </tr>
  {{foreach from=$usermessages item=_usermessage}}
    {{assign var=usermessage_id value=$_usermessage->_id}}

    {{if $mode != 'draft'}}
      {{assign var=onclick value="UserMessage.view('$usermessage_id');"}}
    {{else}}
      {{assign var=onclick value="UserMessage.edit('$usermessage_id', null, '$inputMode', UserMessage.refreshListCallback);"}}
    {{/if}}
    <tr class="alternate message{{if !$_usermessage->_ref_dest_user->datetime_read && $mode == 'inbox'}} unread{{/if}}">
      <td class="narrow">
        <input type="checkbox" value="{{$_usermessage->_ref_dest_user->_id}}"/>
      </td>
      <td onclick="{{$onclick}}">
        {{if $_usermessage->_ref_dest_user && $_usermessage->_ref_dest_user->_id}}
          {{$_usermessage->_ref_dest_user->_datetime_sent}}
        {{elseif $mode == 'sentbox'}}
          {{assign var=dest_user value=$_usermessage->_ref_destinataires|@first}}
          {{$dest_user->_datetime_sent}}
        {{/if}}
      </td>
      <td onclick="{{$onclick}}">
        {{if $_usermessage->_mode == "out"}}  <!-- envoi -->
          <div class="me-display-flex me-flex-wrap">
            {{foreach from=$_usermessage->_ref_destinataires item=_dest name=user_message_recipients}}
              {{if $smarty.foreach.user_message_recipients.iteration <= 4}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_dest->_ref_user_to}}
              {{/if}}
            {{/foreach}}
            {{if $_usermessage->_ref_destinataires|@count > 4}}
              {{math assign=hidden_recipients_number equation="x-4" x=$_usermessage->_ref_destinataires|@count}}
              <div class="me-user-chips"><div>{{tr var1=$hidden_recipients_number}}CUserMessageDest-msg-recipients_number{{/tr}}</div></div>
            {{/if}}
          </div>
        {{else}}    <!-- reception -->
          {{if $_usermessage->_ref_dest_user && $_usermessage->_ref_dest_user->_id && $_usermessage->_ref_dest_user->_ref_user_from->_id}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_usermessage->_ref_dest_user->_ref_user_from}}
          {{/if}}
        {{/if}}
      </td>
      <td onclick="{{$onclick}}">
        {{if $_usermessage->_ref_dest_user && $_usermessage->_ref_dest_user->_id && $_usermessage->_mode == 'in' && $_usermessage->_ref_dest_user->starred}}
          <i style="float: right; color: #ffa306; margin : 2px;" class=" fa fa-star"></i>
        {{/if}}
        {{if count($_usermessage->_ref_attachments)}}
          <i class="msgicon fa fa-paperclip" style="font-size: 1.5em; float: right; margin-right: 2px;" title="{{tr}}Attachments{{/tr}} : {{$_usermessage->_ref_attachments|@count}}"></i>
        {{/if}}

        <a href="#">{{$_usermessage->subject}}</a>
      </td>
      <td onclick="{{$onclick}}">
        {{$_usermessage->_abstract}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CUserMessage.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination total=$total current=$page change_page="UserMessage.refreshListPage" step=$app->user_prefs.nbMailList}}
    </td>
  </tr>
</table>
