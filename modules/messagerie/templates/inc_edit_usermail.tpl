{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=UserEmail}}
{{mb_script module=files script=files}}

<script type="text/javascript">
  displayCCField = function(elt) {
    elt.hide();
    if (!$('addBCC').visible()) {
      $('row_add_cc_bcc').hide();
    }
    $('cc').show();
  };

  displayBCCField = function(elt) {
    elt.hide();
    if (!$('addCC').visible()) {
      $('row_add_cc_bcc').hide();
    }
    $('bcc').show();
  };

  sendMail = function(form) {
    $V(form._content, CKEDITOR.instances.htmlarea.getData());
    $V(form.action, 'send');
    $V(form._closeModal, 1);
    return onSubmitFormAjax(form);
  };

  displayAddress = function(field, address) {
    var elt = $(field + '_addresses');
    var html = '<span class="circled" data-address="' + address + '">' + address + '<span onclick="removeAddress(\'' + field + '\', this.up());" style="margin-left: 5px; cursor: pointer;"><i class="msgicon fa fa-times"></i></span></span>';
    elt.insert(html);
  }

  addAddress = function(field) {
    var tmp_field = $('edit-userMail__' + field);
    var address = $V(tmp_field);
    $V(tmp_field, '');
    var elt = $('edit-userMail_' + field);
    if ($V(elt) != '') {
      var addresses = $V(elt).split(',');
      addresses.push(address);
      $V(elt, addresses.join(','));
    }
    else {
      $V(elt, address);
    }
    displayAddress(field, address);
  };

  removeAddress = function(field, address) {
    var field_elt = $('edit-userMail_' + field);
    var recipients = $V(field_elt).split(',');
    recipients.splice(recipients.indexOf(address.getAttribute('data-address')), 1);
    $V(field_elt, recipients.join(','));
    address.remove();
  };

  deleteMail = function(form) {
    $V(form.del, 1);
    $V(form._closeModal, 1);
    onSubmitFormAjax(form, {check: function() {
      return true;
    }});
  };

  draftMail = function(form) {
    $V(form.action, 'draft');
    $V(form._content, CKEDITOR.instances.htmlarea.getData());
    return onSubmitFormAjax(form);
  }

  {{if 'apicrypt'|module_active && $account->name|strpos:'apicrypt' !== false}}
    searchRecipient = function(field) {
        var url = new Url('apicrypt', 'ajax_view_search_recipient');
        url.addParam('field', field);
        url.requestModal(null, null, {showClose: true});
      };
  {{elseif 'messagerie access ldap_directory'|gconf}}
    searchRecipient = function(field) {
      var url = new Url('messagerie', 'ajax_view_search_recipient');
      url.addParam('field', field);
      url.requestModal(null, null, {showClose: true});
    };
  {{/if}}

  Main.add(function() {
    var to = '{{$mail->to}}';
    to.split(',').each(function(address) {
      if (address != '') {
        displayAddress('to', address);
      }
    });

    var cc = '{{$mail->cc}}';
    cc.split(',').each(function(address) {
      if (address != '') {
        displayAddress('cc', address);
      }
    });

    var bcc = '{{$mail->bcc}}';
    bcc.split(',').each(function(address) {
      if (address != '') {
        displayAddress('bcc', address);
      }
    });

    /* Modification de la taille de CkEditor */
    if (window.CKEDITOR) {
      window.CKEDITOR.on("instanceReady", function() {
        if ($('cke_htmlarea') && $('cke_htmlarea').down('div.cke_inner') && $('cke_htmlarea').down('div.cke_contents')) {
          $('cke_htmlarea').down('div.cke_contents').setStyle({maxHeight: '365px'});
          $('cke_htmlarea').down('div.cke_inner').setStyle({maxHeight: '395px'});
        }
      });
    }
  });
</script>

<form name="edit-userMail" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$mail}}
  {{mb_key object=$mail}}

  <input type="hidden" name="m" value="messagerie"/>
  <input type="hidden" name="dosql" value="do_usermail_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="action" value=""/>
  <input type="hidden" name="_closeModal" id="closeModal" value="0"/>
  <input type="hidden" name="callback" value="callbackModalMessagerie"/>
  {{if 'apicrypt'|module_active && $account->name|strpos:'apicrypt' !== false}}
    <input type="hidden" name="is_apicrypt" value="1"/>
  {{/if}}

  {{mb_field object=$mail field=account_id hidden=true}}
  {{mb_field object=$mail field=account_class hidden=true}}
  {{mb_field object=$mail field=draft hidden=true}}
  {{mb_field object=$mail field=in_reply_to_id hidden=true}}

  <div style="height: 15%; width: 100%; position: relative; margin-top: 5px;">
    <table id="message-header" class="form">
      <tr>
        <th>{{mb_label object=$mail field=to}}</th>
        <td>
          <input type="email" class="email" name="_to" value="" size="50"/>
          <button type="button" class="add notext" onclick="addAddress('to');">{{tr}}Add{{/tr}}</button>
          {{if 'messagerie access ldap_directory'|gconf}}
            <button type="button" style="margin-left: 0px; padding: 1px;" class="search notext" onclick="searchRecipient('to');">{{tr}}Search{{/tr}}</button>
          {{/if}}
          <div id="to_addresses"></div>
          {{mb_field object=$mail field=to class='notNull' hidden=true}}
        </td>
      </tr>
      <tr id="row_add_cc_bcc">
        <td></td>
        <td>
          <button type="button" class="add" id="addCC" onclick="displayCCField(this);">
            {{tr}}CUserMail-msg-add_cc{{/tr}}
          </button>
          <button type="button" id="addBCC" class="add" onclick="displayBCCField(this);">
            {{tr}}CUserMail-msg-add_bcc{{/tr}}
          </button>
        </td>
      </tr>
      <tr id="cc" style="display: none;">
        <th>{{mb_label object=$mail field=cc}}</th>
        <td>
          <input type="email" class="email" name="_cc" value="" size="50"/>
          <button type="button" class="add notext" onclick="addAddress('cc');">{{tr}}Add{{/tr}}</button>
          {{if 'messagerie access ldap_directory'|gconf}}
            <button type="button" style="margin-left: 0px; padding: 1px;" class="search notext" onclick="searchRecipient('cc');">{{tr}}Search{{/tr}}</button>
          {{/if}}
          {{mb_field object=$mail field=cc hidden=true}}
          <div id="cc_addresses"></div>
        </td>
      </tr>
      <tr id="bcc" style="display: none;">
        <th>{{mb_label object=$mail field=bcc}}</th>
        <td>
          <input type="email" class="email" name="_bcc" value="" size="50"/>
          <button type="button" class="add notext" onclick="addAddress('bcc');">{{tr}}Add{{/tr}}</button>
          {{if 'messagerie access ldap_directory'|gconf}}
            <button type="button" style="margin-left: 0px; padding: 1px;" class="search notext" onclick="searchRecipient('bcc');">{{tr}}Search{{/tr}}</button>
          {{/if}}
          {{mb_field object=$mail field=bcc hidden=true}}
          <div id="bcc_addresses"></div>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$mail field=subject}}</th>
        <td>{{mb_field object=$mail field=subject size=50 class='notNull'}}</td>
      </tr>
      <tr>
        <th><label for="_attachments">{{tr}}Attachments{{/tr}}</label></th>
        <td style="height: 25px;">
          <input type="hidden" value="" name="_attachments"/>
          <button type="button" style="padding: 1px; display: inline; vertical-align: 25%; height: 25px;"
                  class="notext" onclick="UserEmail.addAttachment('{{$mail->_id}}');">
            <i class="msgicon fa fa-2x fa-paperclip"></i>
          </button>
          <span id="list_attachments">
            {{mb_include module=messagerie template=inc_mail_attachments attachments=$mail->_attachments}}
          </span>
        </td>
      </tr>
      <tr>
        <td colspan="6" class="message_input" style="width: 100%; max-height: 400px;">
          {{mb_field object=$mail field=_content id="htmlarea" style="height: 400px;"}}
        </td>
      </tr>
      <tr>
        <td colspan="6" class="button">
          <button type="button" id="btn_send_email" onclick="sendMail(this.form);">
            <i class="msgicon fas fa-paper-plane"></i>
            {{tr}}Send{{/tr}}
          </button>
          <button type="submit" onclick="draftMail(this.form);">
            <i class="msgicon far fa-save"></i>
            {{tr}}Save{{/tr}}
          </button>
          {{if $mail->_id}}
            <button type="button" class="trash" onclick="deleteMail(this.form);">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
          <button type="button" title="{{tr}}Cancel{{/tr}}" onclick="{{if $mail->_id}}window.parent.Control.Modal.close();{{else}}deleteMail(this.form);{{/if}}">
            <i class="msgicon fa fa-times"></i>
            {{tr}}Cancel{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </div>
</form>