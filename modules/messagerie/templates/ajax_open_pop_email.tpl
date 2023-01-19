{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{mb_label object=$mail field=subject}}</th><td style="text-align: left;" colspan="3">{{mb_value object=$mail field=subject}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$mail field=from}}</th><td style="text-align: left;" colspan="3">{{mb_value object=$mail field=from}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$mail field=to}}</th><td style="text-align: left;" colspan="3">{{mb_value object=$mail field=to}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$mail field=date_inbox}}</th><td colspan="3">{{mb_value object=$mail field=date_inbox}}</td>
  </tr>
  <tr>
    <th colspan="4">{{mb_label object=$mail field=text_plain_id}}</th>
  </tr>
  <tr>
    <td colspan="4" style="text-align: left;">
      {{if $mail->text_html_id && $app->user_prefs.ViewMailAsHtml}}
        {{$mail->_text_html->content|smarty:nodefaults}}
      {{elseif $mail->text_plain_id}}
        {{$mail->_text_plain->content|smarty:nodefaults|nl2br}}
      {{else}}
        {{tr}}CUserMail-msg-noContentText{{/tr}}
      {{/if}}
    </td>
  </tr>
{{*
{{if $mail->attachments|count}}
  <tr><th colspan="4">{{tr}}Attachments{{/tr}}</th></tr>
  <style>
    svg,img {
      max-width:100%;
      max-height:30%;
    }
  </style>

  {{foreach from= $mail->attachments key=type item=_attachment}}
    <tr>
      <td style="text-align:center;">{{mb_include template=inc_show_attachments}}</td>
      <td>{{$_attachment->name}}</td>
      <td>{{$_attachment->subtype}}</td>
      <td>{{$_attachment->bytes}} bytes</td>
    </tr>
  {{/foreach}}

{{/if}}
*}}
</table>



