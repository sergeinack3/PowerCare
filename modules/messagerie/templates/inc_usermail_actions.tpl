{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" title="{{tr}}Delete{{/tr}}" onclick="UserEmail.action('delete', '{{$mail->_id}}');">
  <i class="msgicon fas fa-trash-alt"></i>
  {{tr}}Delete{{/tr}}
</button>

{{if !$mail->archived}}
  <button type="button" title="{{tr}}CUserMail-title-archive{{/tr}}" onclick="UserEmail.action('archive', '{{$mail->_id}}');">
    <i class="msgicon fa fa-archive"></i>
    {{tr}}CUserMail-title-archive{{/tr}}
  </button>
{{else}}
  <button type="button" title="{{tr}}CUserMail-title-unarchive{{/tr}}" onclick="UserEmail.action('unarchive', '{{$mail->_id}}');">
    <i class="msgicon fa fa-inbox"></i>
    {{tr}}CUserMail-title-unarchive{{/tr}}
  </button>
{{/if}}

<button type="button" title="{{tr}}CUserMail-action-move{{/tr}}" onclick="UserEmail.selectParentFolder('{{$mail->account_id}}', '{{$mail->_id}}');">
  <span class="fa-stack fa" style="width: 12px; height: 12px;">
    <i class="msgicon fa fa-folder fa-stack-1x" style="top: -6px;"></i>
    <i class="fas fa-long-arrow-alt-right fa-stack-1x" style="color: #fff; font-size: 0.65em; top: -6px;"></i>
  </span>
  {{tr}}CUserMail-action-move{{/tr}}
</button>

<button type="button" title="{{tr}}CUserMail-title-answer{{/tr}}" onclick="UserEmail.edit('{{$mail->_id}}', 0, 0, 1);">
  <i class="msgicon fas fa-arrow-right"></i>
  {{tr}}CUserMail-title-forward{{/tr}}
</button>

<button type="button" title="{{tr}}CUserMail-title-answer{{/tr}}" onclick="UserEmail.edit(null, '{{$mail->_id}}');">
  <i class="msgicon fa fa-reply"></i>
  {{tr}}CUserMail-title-answer{{/tr}}
</button>

<button type="button" title="{{tr}}CUserMail-title-answer_to_all{{/tr}}" onclick="UserEmail.edit(null, '{{$mail->_id}}', 1);">
  <i class="msgicon fa fa-reply-all"></i>
  {{tr}}CUserMail-title-answer_to_all{{/tr}}
</button>

{{if $mail->date_read}}
  <button type="button" title="{{tr}}CUserMail-title-unread{{/tr}}" onclick="UserEmail.action('mark_unread', '{{$mail->_id}}');">
    <i class="msgicon fa fa-eye-slash"></i>
    {{tr}}CUserMail-title-unread{{/tr}}
  </button>
{{else}}
  <button type="button" title="{{tr}}CUserMail-title-read{{/tr}}" onclick="UserEmail.action('mark_read', '{{$mail->_id}}');">
    <i class="msgicon fa fa-eye"></i>
    {{tr}}CUserMail-title-read{{/tr}}
  </button>
{{/if}}

{{if !$mail->favorite}}
  <button type="button" title="{{tr}}CUserMail-title-favour{{/tr}}" onclick="UserEmail.action('favour', '{{$mail->_id}}');">
    <i class="msgicon fa fa-star"></i>
    {{tr}}CUserMail-title-favour{{/tr}}
  </button>
{{else}}
  <button type="button" title="{{tr}}CUserMail-title-unfavour{{/tr}}" onclick="UserEmail.action('unfavour', '{{$mail->_id}}');">
    <i class="msgicon far fa-star"></i>
    {{tr}}CUserMail-title-unfavour{{/tr}}
  </button>
{{/if}}

{{if $app->user_prefs.LinkAttachment}}
  <button type="button" onclick="UserEmail.linkAttachment('{{$mail->_id}}');">
    <i class="msgicon fa fa-link"></i>
    {{tr}}CMailAttachments-button-append{{/tr}}
  </button>
{{/if}}
<button type="button" title="{{tr}}Print{{/tr}}" onclick="UserEmail.print('{{$mail->_id}}');">
    <i class="msgicon fa fa-print"></i>
    {{tr}}Print{{/tr}}
</button>
