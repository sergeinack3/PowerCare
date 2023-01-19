/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var UserMessage = {
  updater: 0,

  edit: function(usermessage_id, destinataire_id, input_mode, oncloseCallback) {
    var url = new Url("messagerie", "ajax_edit_usermessage");
    url.addParam("usermessage_id", usermessage_id);
    url.addParam("usermessage_dest_id", destinataire_id);
    if (input_mode == 'html') {
      url.modal({width: 1080, height: '100%'});
    }
    else {
      url.requestModal(1080, '100%');
    }

    url.modalObject.observe('afterClose', function() {
      if (oncloseCallback) {
        oncloseCallback();
      }
      else {
        window.location.reload();
      }
    });
  },

  view: function(usermessage_id) {
    var url = new Url('messagerie', 'ajax_view_usermessage');
    url.addParam('usermessage_id', usermessage_id);
    url.requestModal(1000, 600, {onClose: UserMessage.refreshListCallback.curry()});
  },

  create: function(to_id, in_reply_to, subject, input_mode, answer_to_all, forward_mail) {
    var url = new Url("messagerie", "ajax_edit_usermessage");
    url.addParam("usermessage_id", 0);
    if (to_id) {
      url.addParam("to_id", to_id);
    }
    if (subject) {
      url.addParam('subject', subject);
    }
    if (in_reply_to) {
      url.addParam("in_reply_to", in_reply_to);
    }
    if (answer_to_all) {
      url.addParam('answer_to_all', answer_to_all);
    }
    if (forward_mail) {
      url.addParam('forward_mail', forward_mail);
    }
    if (input_mode == 'html') {
      url.modal({width: 1080, height: 700, onClose: UserMessage.refreshListCallback.curry()});
    }
    else {
      url.requestModal(1080, 700, {onClose: UserMessage.refreshListCallback.curry()});
    }
  },

  createWithSubject: function(to_id, subject) {
    var url = new Url("messagerie", "ajax_edit_usermessage");
    url.addParam("usermessage_id", 0);
    if (to_id) {
      url.addParam("to_id", to_id);
    }
    if (subject) {
      url.addParam('subject', subject);
    }
    url.modal({width: 1080, height: 700});
  },

  refresh: function(mode, start) {
    if (this.updater) {
      clearInterval(this.updater);
    }

    UserMessage.refreshList(mode, start);
    this.updater = setInterval(UserMessage.refreshListCallback.curry(), 300000);
  },

  refreshList : function(mode, start) {
    var oform = getForm('list_usermessage');
    if (oform) {
      UserMessage.selectBox(mode);
      $V(oform.mode, mode);
      $V(oform.page, start);
      oform.onsubmit();
    }
  },

  refreshListPage : function(page) {
    var oform = getForm("list_usermessage");
    if (oform) {
      $V(oform.page, page? page : 0);
      oform.onsubmit();
    }
  },

  refreshListCallback : function() {
    var oform = getForm('list_usermessage');
    if (oform) {
      oform.onsubmit();
    }
  },

  editAction : function(action, value, user_dest_id) {
    var url = new Url("messagerie", "ajax_do_action_usermessage");
    url.addParam("action", action);
    if (value) {
      url.addParam("value", value);
    }
    if (user_dest_id) {
      url.addParam("user_dest_ids", JSON.stringify([user_dest_id]));
    }
    else {
      url.addParam('user_dest_ids', this.getSelectedMessages())
    }
    url.requestUpdate("systemMsg", {onComplete:
    UserMessage.refreshListCallback});
  },

  selectBox: function(box) {
    var old_icon = $$('div.folder.selected i.folder-icon')[0];
    old_icon.removeClassName('fa-folder-open');
    old_icon.addClassName('fa-folder');
    $$('div.folder.selected')[0].removeClassName('selected');
    $$('div.folder[data-folder=' + box + ']')[0].addClassName('selected');
    var new_icon = $$('div.folder.selected i.folder-icon')[0];
    new_icon.removeClassName('fa-folder');
    new_icon.addClassName('fa-folder-open');
  },

  toggleSelect: function(mainCheckbox) {
    var checkboxes = $$('tr.message input[type=checkbox]');
    checkboxes.each(function(checkbox) {
      checkbox.checked = mainCheckbox.checked;
    });
  },

  getSelectedMessages: function() {
    var selected_messages = $$('tr.message input[type=checkbox]:checked');
    var messages_id = [];

    selected_messages.each(function(message) {
      messages_id.push(message.getAttribute('value'));
    });

    return JSON.stringify(messages_id);
  },

  refreshCounts: function() {
    var url = new Url('messagerie', 'ajax_refresh_counts_usermessages');
    url.requestJSON(function(data) {
      data.each(function (folder) {
        var element = $$('div.folder[data-folder=' + folder.name + ']')[0].down('span.count');
        element.innerHTML = folder.count;
        if (folder.count > 0) {
          element.show();
        }
        else {
          element.hide();
        }
      });
    });
  },
    print: function (message_dest_id) {
        var url = new Url('messagerie', 'ajax_print_messages');
        if (message_dest_id) {
            url.addParam('messages_dest_ids', JSON.stringify([message_dest_id]))
        } else if (this.getSelectedMessages() !== '[]') {
            url.addParam('messages_dest_ids', this.getSelectedMessages())
        }
        else {
            return;
        }
        url.popup(1000, 1000);
    }
};
