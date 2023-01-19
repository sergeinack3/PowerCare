{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=files ajax=true}}

<div id="actions" style="text-align: center; margin-bottom: 5px;" class="me-margin-top-8">
  {{if $mode == 'inbox' || $mode == 'archive'}}
    <button type="button" title="{{tr}}CUserMail-title-answer{{/tr}}"
            onclick="Control.Modal.close();UserMessage.create(0, '{{$usermessage->_id}}', '', '{{$app->user_prefs.inputMode}}', 0 , 1);">
      <i class="msgicon fas fa-arrow-right"></i>
      {{tr}}CUserMail-title-forward{{/tr}}
    </button>

    <button type="button" onclick="Control.Modal.close();UserMessage.create('{{$usermessage->creator_id}}', '{{$usermessage->_id}}', '', '{{$app->user_prefs.inputMode}}');">
      <i class="msgicon fa fa-reply"></i>
      {{tr}}CUserMessage.answer{{/tr}}
    </button>

    {{if $usermessage->_ref_destinataires|@count > 1 && !$usermessage->hidden_recipients}}
      <button type="button" onclick="Control.Modal.close();UserMessage.create('{{$usermessage->creator_id}}', '{{$usermessage->_id}}', '', '{{$app->user_prefs.inputMode}}', 1);">
        <i class="msgicon fa fa-reply-all"></i>
        {{tr}}CUserMessage.answer_to_all{{/tr}}
      </button>
    {{/if}}

    {{if $mode != 'archive'}}
      <button type="button" title="{{tr}}CUserMessageDest-title-to_archive-0{{/tr}}" onclick="UserMessage.editAction('archive', '1', '{{$usermessage->_ref_dest_user->_id}}');">
        <i class="msgicon fa fa-archive"></i>
        {{tr}}CUserMessageDest-title-to_archive-0{{/tr}}
      </button>
    {{/if}}
  {{/if}}

  {{if $mode == 'archive'}}
    <button type="button" title="{{tr}}CUserMessageDest-title-to_archive-1{{/tr}}" onclick="UserMessage.editAction('archive', '0', '{{$usermessage->_ref_dest_user->_id}}');">
      <i class="msgicon fa fa-inbox"></i>
      {{tr}}CUserMessageDest-title-to_archive-1{{/tr}}
    </button>
  {{/if}}

  {{if $mode != 'sentbox'}}
    <button type="button" title="{{tr}}Delete{{/tr}}" onclick="UserMessage.editAction('delete', '', '{{$usermessage->_ref_dest_user->_id}}');">
      <i class="msgicon fas fa-trash-alt"></i>
      {{tr}}Delete{{/tr}}
    </button>
  {{/if}}

  {{if $mode == 'inbox'}}
    <button type="button" title="{{tr}}CUserMessage-title-read{{/tr}}" onclick="UserMessage.editAction('mark_read', '', '{{$usermessage->_ref_dest_user->_id}}');">
      <i class="msgicon fa fa-eye"></i>
      {{tr}}CUserMessageDest-title-read{{/tr}}
    </button>

    <button type="button" title="{{tr}}CUserMessage-title-unread{{/tr}}" onclick="UserMessage.editAction('mark_unread', '', '{{$usermessage->_ref_dest_user->_id}}');">
      <i class="msgicon fa fa-eye-slash"></i>
      {{tr}}CUserMessageDest-title-unread{{/tr}}
    </button>

    {{if $usermessage->_ref_dest_user->starred == 0}}
      <button type="button" title="{{tr}}CUserMessageDest-title-to_star-0{{/tr}}" onclick="UserMessage.editAction('star', '1', '{{$usermessage->_ref_dest_user->_id}}');">
        <i class="msgicon fa fa-star"></i>
        {{tr}}CUserMessageDest-title-to_star-0{{/tr}}
      </button>
    {{else}}
      <button type="button" title="{{tr}}CUserMessageDest-title-to_star-0{{/tr}}" onclick="UserMessage.editAction('star', '0', '{{$usermessage->_ref_dest_user->_id}}');">
        <i class="msgicon far fa-star"></i>
        {{tr}}CUserMessageDest-title-to_star-1{{/tr}}
      </button>
    {{/if}}
      <button type="button" title="{{tr}}Print{{/tr}}" onclick="UserMessage.print('{{$usermessage->_ref_dest_user->_id}}');">
          <i class="msgicon fa fa-print"></i>
          {{tr}}Print{{/tr}}
      </button>
  {{/if}}
</div>

<table class="form" style="width: 100%; margin-top: 5px; margin-bottom: 10px;">
  <tr>
    <th colspan="4" class="title">{{mb_value object=$usermessage field=subject}}</th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}CUserMessageDest-from_user_id{{/tr}}</th>
    <td>
      <div class="mediuser" style="border-color: #{{$usermessage->_ref_user_creator->_ref_function->color}};">
        {{$usermessage->_ref_user_creator}}
      </div>
    </td>
    <th class="narrow">{{tr}}CUserMessageDest-to_user_id{{/tr}}</th>
    <td>
      <div class="me-display-flex me-flex-wrap">
        {{foreach from=$usermessage->_ref_destinataires item=_dest}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_dest->_ref_user_to}}
        {{/foreach}}
      </div>
    </td>
  </tr>
  <tr>
    <th class="narrow">{{tr}}CUserMessageDest-datetime_sent{{/tr}}</th>
    <td>
      {{$usermessage->_ref_dest_user->_datetime_sent}}
    </td>
    <th class="narrow">{{tr}}CUserMessageDest-datetime_read{{/tr}}</th>
    <td>
      {{$usermessage->_ref_dest_user->_datetime_read}}
    </td>
  </tr>
</table>

{{if $usermessage->_ref_attachments}}
  <hr/>
  <div class="me-margin-left-10">
    {{mb_include module=messagerie template=inc_user_message_attachments attachments=$usermessage->_ref_attachments}}
  </div>
{{/if}}

<hr/>

<iframe id="message_content" src="?m=messagerie&raw=get_usermessage_content&usermessage_id={{$usermessage->_id}}"></iframe>
