{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPfiles script=file ajax=1}}

<style>
  #list_dest li button {
    border:none;
    padding:0;
    background: url('style/mediboard_ext/images/buttons/delete-tiny.png') transparent no-repeat;
    width: 11px;
    height:11px;
  }
  #list_dest li {
    list-style: none;
  }
</style>

<script>
  {{if $usermessage->_can_edit}}
    Main.add(function() {
      var form = getForm("edit_usermessage");
      var element = form.elements.keywords;
      new Url('messagerie', 'ajax_autocomplete_receivers')
        .autoComplete(element, null, {
          width: '200px;',
          minChars: 3,
          method: "get",
          select: "view",
          dropdown: true,
          afterUpdateElement: function(field,selected) {
            var id = selected.getAttribute("data-id");
            var name = selected.down('span.view').innerHTML;
            var function_color = selected.down('div', 0).getStyle('border-left');
            var connected = selected.down('i').hasClassName('connected-user');
            addDest(id, name, function_color, 'dest', connected);
            $V(element, '');
          }
      });

      var function_view = form.elements._to_function_view;
      new Url('system', 'ajax_seek_autocomplete')
        .addParam('object_class', 'CFunctions')
        .addParam('input_field', function_view.name)
      {{if "messagerie messagerie_interne resctriction_level_messages"|gconf == "group"}}
          .addParam('where[group_id]', '{{$g}}')
      {{elseif "messagerie messagerie_interne resctriction_level_messages"|gconf == "function"}}
          .addParam('where[function_id]', '{{$usermessage->_ref_user_creator->function_id}}')
      {{/if}}
        .addParam('where[actif]', '1')
        .addParam('show_view', true)
        .autoComplete(function_view, null, {
          width: '200px;',
          minChars: 3,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field,selected) {
            var id = selected.getAttribute("data-id");
            var name = selected.down('span.view').innerHTML;
            var function_color = selected.down('div', 0).getStyle('border-left');
            addDest(id, name, function_color, 'function');
            $V(function_view, '');
          }
      });

      var group_view = form.elements['_to_group_view'];
      new Url('system', 'ajax_seek_autocomplete')
        .addParam('object_class', 'CUserMessageDestGroup')
        .addParam('input_field', group_view.name)
        .addParam('where[group_id]', '{{$g}}')
        .addParam('show_view', true)
        .autoComplete(group_view, null, {
          width: '200px;',
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field,selected) {
            var id = selected.getAttribute("data-id");
            var name = selected.down('span.view').innerHTML;
            var color = selected.down('span.view', 0).getStyle('border-left');
            addDest(id, name, color, 'group');
            $V(group_view, '');
          }
      });
    });
  {{/if}}

  addDest = function(id, name, style, type, connected) {
    var dest_list = $('list_dest');
    var existing = $("dest_" + id);
    if (existing) {
      return;
    }
    $V(getForm('edit_usermessage')._dest, 1);

    var li = DOM.li({id: type + '_' + id, style: 'border-left: ' + style + '; padding-left: 3px; height: 15px', title: $T('CUserMessage.dest_type.' + type)}, name);

    if (type == 'dest') {
      var classname = connected ? 'connected-user' : 'disconnected-user';
      var locale = "CMediusers-msg-" + connected? 'connected' : 'disconnected';
      li.insert(DOM.i({class: 'fas fa-user ' + classname, title: $T(locale), style: 'float: right;'}));
    }

    li.insert(DOM.button({class: 'delete notext', type: 'button', style: 'float: right;', onclick: 'removeDest(\'' + type + '_' + id + '\');'}));
    li.insert(DOM.input({class: type, type: 'hidden', name: type + '[]', value: id}));
    dest_list.insert(li);
  };

  removeDest = function(id) {
    $(id).remove();
    if (!$$('input.dest').length && !$$('input.function').length && !$$('input.group').length) {
      $V(getForm('edit_usermessage')._dest, '');
    }
  };

  deleteAttachment = function(attachment_guid) {
    var form = getForm('delete' + attachment_guid);
    if (form) {
      onSubmitFormAjax(form, function() {
        var element = $(attachment_guid);
        if (element) {
          element.remove();
        }
      });
    }
  };

  submitMessage = function(form, closeModal) {
    var dests = $$('input.dest');
    var functions = $$('input.function');
    var groups = $$('input.group');
    if (dests.length || functions.length || groups.length) {
      if ($V(form.del) == '0') {
        {{if $app->user_prefs.inputMode == 'html'}}
          $V(form.content, CKEDITOR.instances.htmlarea.getData());
        {{/if}}
        /*  We manually add an index to the dest[] input because the onSubmitFormAjax function can't handle the array inputs */
        var index_dest = 0;
        dests.each(function(dest) {
          dest.name = 'dest[' + index_dest + ']';
          index_dest++;
        });

        return onSubmitFormAjax(form);
      }
      else {
        return onSubmitFormAjax(form, {check: function() {
            return true;
        }});
      }
    }
    else {
      alert('{{tr}}CUserMessage-msg-no_recipient_selected{{/tr}}');
      return false;
    }
  }

</script>

<form method="post" action="?" name="edit_usermessage" enctype="multipart/form-data" onsubmit="return submitMessage(this);">
  <input type="hidden" name="m" value="messagerie"/>
  <input type="hidden" name="dosql" value="do_usermessage_aed"/>
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_send" value="0" />
  <input type="hidden" name="_archive" value="0" />
  <input type="hidden" name="_readonly" value="{{if $usermessage->_can_edit}}0{{else}}1{{/if}}" />
  <input type="hidden" name="_dest" value="{{$usermessage->_ref_destinataires|@count}}"/>
  <input type="hidden" name="_functions" value="">
  <input type="hidden" name="_groups" value="">
  <input type="hidden" name="_closeModal" value="0" id="closeModal"/>
  <input type="hidden" name="usermessage_id" value="{{$usermessage->_id}}" />
  <input type="hidden" name="in_reply_to" value="{{$usermessage->in_reply_to}}" />
  <input type="hidden" name="callback" value="callbackModalMessagerie" />

  <table class="main">
    <tr>
      <td id="message_area" style="width:700px;" class="me-message-area">
        <table class="form">
          <tr>
            <th class="narrow">{{mb_label object=$usermessage field=creator_id}}</th>
            <td>
              {{mb_field object=$usermessage field=creator_id hidden=1}}
              <div class="mediuser" style="border-color: #{{$usermessage->_ref_user_creator->_ref_function->color}};">
                {{$usermessage->_ref_user_creator}}
              </div>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$usermessage field=subject}}</th>
            <td>
              {{if !$usermessage->_can_edit}}
                {{mb_value object=$usermessage field=subject}}
              {{else}}
                {{mb_field object=$usermessage field=subject size=60}}
              {{/if}}
            </td>
          </tr>
          {{if $usermessage->_can_edit}}
          <tr>
            <th>{{mb_label object=$usermessage field=hidden_recipients}}</th>
            <td>
              {{mb_field object=$usermessage field=hidden_recipients}}
            </td>
          </tr>
          {{/if}}
          <tr>
            <th>{{tr}}Attachments{{/tr}}</th>
            <td>
              {{if $usermessage->_ref_attachments}}
                <span id="listAttachments">
                  {{mb_include module=messagerie template=inc_user_message_attachments attachments=$usermessage->_ref_attachments edit=$usermessage->_can_edit}}
                </span>
              {{/if}}
            </td>
          </tr>
          <tr>
            <td colspan="2">
              {{mb_include module=system template=inc_inline_upload}}
            </td>
          </tr>
          <tr>
            <td colspan="2" style="height: 180px">{{mb_field object=$usermessage field=content id="htmlarea"}}</td>
          </tr>
        </table>
      </td>
      <td id="dest_area">
        <h2>{{tr}}CUserMessage-back-usermessage_destinataires{{/tr}}</h2>
        {{if $usermessage->_can_edit}}
          <table class="layout">
            <tr>
              <td>
                <label for="keywords">{{tr}}CUser{{/tr}}</label> :
              </td>
              <td>
                <input type="text" name="keywords" />
              </td>
            </tr>
            <tr>
              <td>
                <label for="_to_function_view">{{tr}}CFunctions{{/tr}}</label> :
              </td>
              <td>
                <input type="text" name="_to_function_view" />
              </td>
            </tr>
            <tr>
              <td>
                <label for="_to_group_view">{{tr}}CUserMessageDestGroup{{/tr}}</label> :
              </td>
              <td>
                <input type="text" name="_to_group_view" />
              </td>
            </tr>
          </table>
        {{/if}}
        <ul id="list_dest">
          {{foreach from=$usermessage->_ref_destinataires item=_dest}}
            <li id="dest_{{$_dest->_ref_user_to->_id}}">
              <span class="mediuser" style="border-color: #{{$_dest->_ref_user_to->_ref_function->color}};">
                {{$_dest->_ref_user_to}} {{if $_dest->datetime_read}}(lu){{/if}}
              </span>

              {{if $usermessage->_can_edit}}
                <input type="hidden" class="dest" name="dest[]" value="{{$_dest->_ref_user_to->_id}}"/>
                <button class="delete notext" type="button" style="float: right;" onclick="removeDest('dest_{{$_dest->_ref_user_to->_id}}');"></button>
              {{/if}}
            </li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $usermessage->_can_edit}}
          <button class="send" type="button" onclick="$V(this.form._send, 1); $V(this.form._closeModal, 1); this.form.onsubmit();">
            {{tr}}Send{{/tr}}
          </button>
          <button type="button" class="save" onclick="this.form.onsubmit();">
            {{tr}}Save{{/tr}}
          </button>
          {{if $usermessage->_id}}
            <button type="button" class="trash" onclick="$V(this.form.del, 1); $V(this.form._closeModal, 1); this.form.onsubmit();">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        {{else}}
          <button type="button" onclick="window.parent.Control.Modal.close(); window.parent.UserMessage.create('{{$usermessage->creator_id}}', '{{$usermessage->_id}}');">
            <i class="msgicon fa fa-reply"></i>
            {{tr}}CUserMail-button-answer{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="cancel" onclick="window.parent.Control.Modal.close();">
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

{{if $usermessage->_id && $usermessage->_ref_attachments}}
  {{foreach from=$usermessage->_ref_attachments item=_attachment}}
    <form name="delete{{$_attachment->_guid}}" action="?" method="post" onsubmit="return false;">
      {{mb_class object=$_attachment}}
      {{mb_key object=$_attachment}}
      <input type="hidden" name="del" value="1">
    </form>
  {{/foreach}}
{{/if}}
