{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPfiles" script="files" ajax=true}}
{{mb_script module=patients    script=pat_selector    ajax=true}}

<script type="text/javascript">
  deleteAttachment = function(form) {
    return onSubmitFormAjax(form, function() {
      Control.Modal.close();
      UserEmail.modalExternalOpen('{{$mail->_id}}', '{{$mail->account_id}}');
    });
  };

  {{if ($mail->_is_hprim || $mail->is_apicrypt) && !$mail->linked_patient_id}}
      Main.add(function() {
        UserEmail.linkAttachment('{{$mail->_id}}');
      });
  {{/if}}
</script>

<div id="actions" class="me-margin-top-8" style="text-align: center; margin-bottom: 5px;">
  {{mb_include module=messagerie template=inc_usermail_actions}}
</div>

<table class="form">
  <tr>
    <th class="title" colspan="4">{{mb_value object=$mail field=subject}}</th>
  </tr>
  <tr>
    <th class="narrow">{{mb_label object=$mail field=from}}</th><td style="text-align: left;">{{mb_value object=$mail field=from}}</td>
    <th>{{mb_label object=$mail field=to}}</th><td style="text-align: left;">{{mb_value object=$mail field=to}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$mail field=date_inbox}}</th><td>{{mb_value object=$mail field=date_inbox}}</td>
    <th>{{mb_label object=$mail field=date_read}}</th><td>{{mb_value object=$mail field=date_read}}</td>
</table>
<hr/>
      <style>
        #content-html iframe, #content-plain {height:{{if !$mail->_attachments|count}}500{{else}}490{{/if}}px; overflow: auto;}
        #content-html td { padding:0; margin: 0; border:0; }
        div.gmail_quote,div.moz-forward-container { margin-left:10px; margin-top:20px; padding-left: 10px; border-left: grey 2px solid;  }
        #content-html iframe img {max-width: 90%;}

        #content-html iframe *{font-size: 11px;}

      </style>

      {{if $mail->text_html_id && $app->user_prefs.ViewMailAsHtml}}
        <div style="text-align: left;" id="content-html">
          {{if $mail->_text_html->content == ''}}
            {{tr}}CUserMail-msg-noContentText{{/tr}}
          {{else}}
            <iframe src="?m={{$m}}&raw=vw_html_content&mail_id={{$mail->_id}}" style="width:100%;"></iframe>
          {{/if}}
        </div>
      {{elseif $mail->text_plain_id}}
        <div style="text-align: left;" id="content-plain">
          {{$mail->_text_plain->content|nl2br|purify}}
        </div>
      {{else}}
        <h1>{{tr}}CUserMail-msg-noContentText{{/tr}}</h1>
      {{/if}}


    {{if $mail->is_apicrypt}}
      <div class="small-warning">
        <img src="modules/{{$m}}/images/cle.png" alt=""/> {{tr var1="apicrypt"}}CUserMail-isApicrypt{{/tr}} <strong>apicrypt</strong>
      </div>
    {{/if}}
    {{if $mail->_is_hprim}}
      {{tr var1="apicrypt"}}CUserMail-hashprim{{/tr}}
      {{mb_include module="hprim21" template="inc_hprim_header"}}
    {{/if}}


{{if $mail->_attachments|count}}
  <table class="form">
    <tr>
      <th class="title">
        {{tr}}Attachments{{/tr}} ({{$nbAttachPicked}}/{{$nbAttachAll}})
        {{if $nbAttachPicked != $nbAttachAll}}
          <button type="button" tilte="{{tr}}CMailAttachments-button-getAllAttachments-desc{{/tr}}" onclick="UserEmail.getAttachment('{{$mail->_id}}','0')" class="button">
            <i class="msgicon fa fa-download"></i>
            {{tr}}CMailAttachments-button-getAllAttachments{{/tr}}
          </button>
        {{/if}}
      </th>
    </tr>
  </table>
  <ul id="list_attachment">
    <style>
      #list_attachment {
        height:170px;
        overflow: auto;
      }

      #list_attachment p{
        height:85px;
        margin-bottom: 0;
      }

      .attachments_list svg,.attachments_list img {
        max-width:200px;
        max-height:70px;
        box-shadow: -2px -2px 2px grey;
      }

      .attachments_list {
        list-style: none;
        width:210px;
        height:130px;
        float:left;
      }
    </style>
    {{foreach from=$mail->_attachments key=key item=_attachment}}
        {{if $_attachment->_file->_id}}
          <li class="attachments_list">
            {{assign var=file value=$_attachment->_file}}
            <p>
                <a onclick="popFile('{{$file->object_class}}', '{{$file->object_id}}', 'CFile', '{{$file->_id}}', '0');"  href="#" title="{{tr}}CMailAttachments-openAttachment{{/tr}}">
                  {{thumbnail document=$file profile=medium alt="Preview" default_size=1}}<br/>
                {{$file->file_name}} ({{$file->_file_size}})
                </a>
            </p>
              <form name="editFile{{$file->_id}}" action="?" target="#" method="post" onsubmit="return deleteAttachment(this);">
              <input type="hidden" name="m" value="files" />
              <input type="hidden" name="dosql" value="do_file_aed" />
              <input type="hidden" name="del" value="1" />
              <input type="hidden" name="file_id" value="{{$file->_id}}"/>
              <button type="button" onclick="this.form.onsubmit();" title="{{tr}}Delete{{/tr}}">
                <i class="msgicon fas fa-trash-alt"></i>
                {{tr}}Delete{{/tr}}
              </button>
            </form>
          </li>
        {{else}}
            <li class="attachments_list">
              <p>
                <a href="#{{$_attachment->_id}}" onclick="UserEmail.getAttachment('{{$mail->_id}}', '{{$_attachment->_id}}')" style="text-align: center;">
                  <img src="images/pictures/unknown.png" style="height:100px;" alt=""/><br/>
                  {{$_attachment->name}} ({{$_attachment->_size}})
                </a>
              </p>
              <button type="button" class=" singleclick" onclick="UserEmail.getAttachment('{{$mail->_id}}', '{{$_attachment->_id}}')">
                <i class="msgicon fa fa-download"></i>
                {{tr}}CMailAttachments-button-getTheAttachment{{/tr}}
              </button>
            </li>
            <td></td>
        {{/if}}

    {{/foreach}}
  </ul>
{{/if}}
