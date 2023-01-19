{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="user_messages_search" style="margin: 5px; vertical-align: middle; text-align: center;">
  {{mb_include module=messagerie template=inc_user_mail_search}}
</div>

<div id="user_messages_actions" style="margin: 5px;">
  {{mb_include module=messagerie template=inc_usermails_actions}}
</div>

<table class="tbl">
  {{if !$account_pop->active}}
    <tr>
      <td colspan="6"><div class="small-warning">{{tr}}CSourcePOP-msg-notActive{{/tr}}</div></td>
    </tr>
  {{/if}}

  <tr>
    <th  class="narrow"{{if $user->isAdmin()}} colspan="2"{{/if}}></th>
    <th style="width: 30px;">Date</th>
    <th>{{if $type == 'sentbox'}}{{tr}}CUserMail-to{{/tr}}{{else}}{{tr}}CUserMail-from{{/tr}}{{/if}}</th>
    <th>{{tr}}CUserMail-subject{{/tr}}</th>
    <th>{{tr}}CUserMail-abstract{{/tr}}</th>
  </tr>
  <tbody>
  {{if $nb_mails != 0}}
    <tr>
      <td colspan="6">
        {{mb_include module=system template=inc_pagination total=$nb_mails current=$page change_page="UserEmail.refreshListPage" step=$app->user_prefs.nbMailList}}
      </td>
    </tr>
  {{/if}}
    {{foreach from=$mails item=_mail}}
      {{assign var=_mail_from value="@"|explode:$_mail->_from}}
      {{assign var=_mail_id value=$_mail->_id}}
      {{assign var=onclick value="UserEmail.modalExternalOpen('$_mail_id','$account_id');"}}
      {{if $type == 'drafts'}}
        {{assign var=onclick value="UserEmail.edit('$_mail_id');"}}
      {{/if}}
      {{assign var=style_msg value=""}}
      {{if !$_mail->date_read}}
        {{assign var=style_msg value="font-weight: bold;"}}
      {{/if}}
      <tr class="message alternate">
        <td class="button">
          <input type="checkbox" name="item_mail" value="{{$_mail->_id}}" />
        </td>
        {{if $user->isAdmin()}}
          <td style="{{$style_msg}}">
            {{if $_mail->uid}}
              <button type="button" onclick="UserEmail.openMailDebug('{{$_mail->_id}}');">
                <i class="msgicon fa fa-wrench"></i>
              </button>
            {{/if}}
          </td>
        {{/if}}
        <td onclick="{{$onclick}}" style="{{$style_msg}}">
          <span title="{{mb_value object=$_mail field=date_inbox}}">
            {{$_mail->_date_inbox}}
          </span>
        </td>
        <td class="text" onclick="{{$onclick}}" style="{{$style_msg}} line-height: 0">
          {{if $type == 'sentbox'}}
            <label title="{{$_mail->to}}">{{$_mail->_to}}</label>
          {{else}}
            <label title="{{$_mail->from}}" class="me-line-height-16">{{$_mail_from[0]}}</label>
            {{if isset($_mail_from[1]|smarty:nodefaults)}}
              <br>
              <label title="{{$_mail->from}}" class="me-line-height-16" style="color: #8D8D8D">@{{$_mail_from[1]}}</label>
            {{/if}}
          {{/if}}
        </td>
        {{assign var=subject value=$_mail->subject}}
        <td class="text {{if !$subject}}empty{{/if}}" onclick="{{$onclick}}" style="{{$style_msg}}">
          {{if $_mail->favorite && $type != 'favorites'}}
            <i class="msgicon fa fa-star" style="font-size: 1.5em; float: right; color: #ffa306; margin-right: 2px;" title="{{tr}}CUserMail-favorite{{/tr}}"></i>
          {{/if}}
          {{if count($_mail->_attachments)}}
            <i class="msgicon fa fa-paperclip" style="font-size: 1.5em; float: right; margin-right: 2px;" title="{{tr}}Attachments{{/tr}} : {{$_mail->_attachments|@count}}"></i>
          {{/if}}
          {{if $_mail->is_apicrypt}}
            <img title="apicrypt" src="modules/messagerie/images/cle.png" alt="Apicrypt" style="height:15px; float: right; margin-right: 2px;"/>
          {{/if}}
          {{if $_mail->is_hprimnet}}
            <i class="msgicon fa fa-key" style="font-size: 1.2em; float: right; margin-right: 2px;" title="HPRIM.Net"></i>
          {{/if}}
          {{if $_mail->to_send}}
            <i class="msgicon fa fa-hourglass" style="font-size: 1.2em; float: right; margin-right: 2px;" title="{{tr}}CUserMail-to_send{{/tr}}"></i>
          {{/if}}
          {{if $_mail->retry_count >= "messagerie messagerie_externe retry_number"|gconf}}
            <i class="fa fa fa-exclamation-circle" style="font-size: 1.2em; float: right; margin-right: 2px; color: #8c0000;" title="{{tr}}CUserMail-msg-number_retries_exceeded{{/tr}}"></i>
          {{/if}}
          <a href="#{{$_mail->_id}}" style="display: inline; vertical-align: middle;">
            {{if $subject}}{{mb_include template=inc_vw_type_message}}{{else}}{{tr}}CUserMail-no_subject{{/tr}}{{/if}}
          </a>
        </td>
        <td onclick="{{$onclick}}"{{if $_mail->_text_plain->content == ""}} class="empty">({{tr}}CUserMail-content-empty{{/tr}}){{else}} class="text compact me-color-black-high-emphasis" style="{{$style_msg}}">{{$_mail->_text_plain->content|truncate:256|smarty:nodefaults|purify}}{{/if}}</td>
      </tr>
    {{foreachelse}}
      <tr><td class="empty" colspan="6"">{{tr}}CUserMail-none{{/tr}}</td></tr>
    {{/foreach}}

    {{if $nb_mails != 0}}
    <tr>
      <td colspan="6">
        {{mb_include module=system template=inc_pagination total=$nb_mails current=$page change_page="UserEmail.refreshListPage" step=$app->user_prefs.nbMailList}}
      </td>
    </tr>
    {{/if}}
  </tbody>
</table>
