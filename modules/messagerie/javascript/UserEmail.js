/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function UserEmail
 */
UserEmail = {
  module:"messagerie",
  mode : "unread",
  account_id : '0',
  url:   null,
  account_url: null,
  tab:  null,
  page: 0,

  modalExternalOpen:function (id, account) {
    var url = new Url(UserEmail.module, "ajax_open_external_email");
    url.addParam("mail_id", id);
    url.requestModal(-20, -20, {onClose: UserEmail.refreshList.curry(null, null)});
  },

  //refresh div
  refreshAccount:function(account_id) {
    var url = new Url(UserEmail.module, "vw_user_external_mail");
    url.addParam("account_id", account_id);
    UserEmail.account_id = account_id;
    url.requestUpdate("account");
  },

  refreshList:function (account_id, mode, page) {
    var url = new Url(UserEmail.module, "ajax_list_mails");

    if (!account_id) {
      account_id = UserEmail.account_id;
    }

    UserEmail.mode       = (mode != null)? mode : UserEmail.mode;
    UserEmail.page       = (page != null)? page : UserEmail.page;

    url.addParam("account_id", UserEmail.account_id);
    url.addParam("folder", UserEmail.mode);
    url.addParam("page", UserEmail.page);

    var keywords = '';
    var options = {
      subject: true,
      from   : true,
      to     : false,
      body   : false,
      range  : 'selected'
    }
    var search_form = getForm('searchMessages');
    var options_form = getForm('searchOptions');
    if (search_form && options_form) {
      keywords = $V(search_form.elements['keywords']);
      options.subject = options_form.elements['query_subject'].checked;
      options.from    = options_form.elements['query_from'].checked;
      options.to      = options_form.elements['query_to'].checked;
      options.body    = options_form.elements['query_body'].checked;
      options.range   = $V(options_form.elements['query_range']);
    }

    url.addParam('search', keywords);
    url.addParam('query_options', Object.toJSON(options));


    url.requestUpdate('list-messages', {onComplete: UserEmail.refreshCounts});
  },

  refreshListPage : function(page) {
    this.refreshList(null, null, page);
  },

  /**
   * Do an action for one or several mails
   *
   * @param string action  The action to perform
   * @param int    mail_id (Optional) The id of a mail
   */
  action : function(action, mail_id) {
    var url = new Url('messagerie', 'ajax_do_action_usermail');
    url.addParam('action', action);

    if (mail_id) {
      url.addParam('usermail_ids', JSON.stringify([mail_id]));
    }
    else {
      url.addParam('usermail_ids', this.getSelectedMails());
    }

    url.requestUpdate('systemMsg', {onComplete: UserEmail.refreshList.curry()});
  },

  selectParentFolder: function(account_id, mail_id) {
    var url = new Url('messagerie', 'ajax_move_usermail');
    url.addParam('account_id', account_id);

    if (mail_id) {
      url.addParam('usermail_ids', JSON.stringify([mail_id]));
    }
    else {
      url.addParam('usermail_ids', this.getSelectedMails());
    }

    url.requestModal(null, null, {onClose: UserEmail.refreshList.curry()});
  },

  moveMail: function(folder_id, mail_id) {
    var url = new Url('messagerie', 'ajax_do_action_usermail');
    url.addParam('action', 'move');
    url.addParam('folder_id', folder_id);

    if (mail_id) {
      url.addParam('usermail_ids', JSON.stringify([mail_id]));
    }
    else {
      url.addParam('usermail_ids', this.getSelectedMails());
    }

    url.requestUpdate('systemMsg', {onComplete: Control.Modal.close.curry()});
  },

  resetSearchFilters: function() {
    var form = getForm('searchMessages');
    if (form) {
      $V(form.elements['keywords'], '');

      $$('div#advanced_search_tooltip input[name="checkbox"]').each(function (input) {
        if (input.name == 'query_subject' || input.name == 'query_from') {
          input.checked = true;
        }
        else {
          input.checked = false;
        }
      });

      $V(form.elements['query_range'], 'actual');
    }
  },

  /**
   * Create a new mail, or edit one
   *
   * @param int  mail_id       (Optional) The id of the mail to edit, if not given, a ne mail will be created
   * @param int  reply_to_id   (Optional) The id of the mail to answer
   * @param bool answer_to_all (Optional) True for answering to all the recipients
   * @param bool forward_mail  (Optional) True for forwarding an email
   */
  edit: function(mail_id, reply_to_id, answer_to_all, forward_mail) {
    var url = new Url('messagerie', 'ajax_edit_usermail');
    url.addParam('account_id', UserEmail.account_id);
    if (mail_id) {
      url.addParam('mail_id', mail_id);
    }
    if (reply_to_id) {
      url.addParam('reply_to_id', reply_to_id);
    }
    if (answer_to_all) {
      url.addParam('answer_to_all', answer_to_all);
    }
    if (forward_mail) {
      url.addParam('forward_mail', forward_mail);
    }

    url.modal({width: -40, height: -40});
    url.modalObject.observe('afterClose', function() {
      UserEmail.refreshList()
    });
  },

  toggleFavorite : function(mail_id) {
    var url = new Url(UserEmail.module, "controllers/do_toggle_favorite");
    url.addParam("mail_id", mail_id);
    url.requestUpdate("systemMsg", function() {
      UserEmail.refreshList();
    });
  },

  toggleArchived : function(mail_id) {
    var url = new Url(UserEmail.module, "controllers/do_toggle_archived");
    url.addParam("mail_id", mail_id);
    url.requestUpdate("systemMsg", function() {
      UserEmail.refreshList();
    });
  },

  /**
   * Return the ids of the selected mails
   *
   * @returns string
   */
  getSelectedMails: function() {
    var selected_mails = $$('tr.message input[type=checkbox]:checked');
    var mails_id = [];

    selected_mails.each(function(message) {
      mails_id.push(message.getAttribute('value'));
    });

    return JSON.stringify(mails_id);
  },

  openMailDebug: function(mail_id) {
    var url = new Url(UserEmail.module, 'vw_pop_mail');
    url.addParam('id', mail_id);
    url.requestModal();
  },

  /**
   * refresh the list of attachments to link
   *
   * @param mail_id
   */
  listAttachLink : function(mail_id, rename) {
    var url = new Url(UserEmail.module, "ajax_list_attachments");
    url.addParam("mail_id", mail_id);
    url.addParam("rename", rename);
    url.requestUpdate("list_attachments");
  },

  getLastMessages:function (account_id) {
    var url = new Url(UserEmail.module, "cron_update_pop");
    url.addParam("account_id", account_id);
    url.requestUpdate("systemMsg", function () {
      UserEmail.refreshAccount(account_id);
    });
  },

  /*
   * Get the attachment from POP serveur but not save it in mediboard, only for preview
   */
  AttachFromPOP:  function (mail_id, part) {
    var url = new Url("messagerie", "get_pop_attachment");
    url.addParam("mail_id", mail_id);
    url.addParam("part", part);
    url.requestModal(800, 800);
  },

  getAttachment:function (mail_id,all) { //récupère les attachments et les lie aux CMailAttachment (création des CFile)
    var url = new Url("messagerie", "pop_attachment_to_cfile");
    url.addParam("mail_id", mail_id);
    url.addParam("attachment_id", all);
    url.requestUpdate("systemMsg", function () {
      Control.Modal.close();
      UserEmail.modalExternalOpen(mail_id);
    });
  },


  markallAsRead: function (account_id) {
    var url = new Url("messagerie", "controllers/do_mark_all_mail_as_read");
    url.addParam("account_id", account_id);
    url.requestUpdate("systemMsg", function () {
      UserEmail.refreshList();
    });
  },

  /**
   * Toggle a list of checkbox
   *
   * @param table_id
   * @param status
   * @param item_class
   */
  toggleSelect: function (table_id, status, item_class) {
    var table = $(table_id);
    table.select("input[name=" + item_class + "]").each(function (elt) {
      elt.checked = status ? "checked" : "";
    });
  },

  /**
   * Link a list of attachment to a folder.
   */
  linkAttachment:function (mail_id, pat_id) {
    var url = new Url("messagerie", "ajax_link_attachments");
    url.addParam("mail_id", mail_id);
    if (pat_id) {
      url.addParam('pat_id', pat_id);
    }
    url.requestModal(800,600);
  },

  dolinkAttachment: function (attach, mail_id) {
    var url = new Url("messagerie", "ajax_do_link_attachments");
    url.addParam("objects", attach.objects.join('|'));
    url.addParam("attach_list", attach.files);
    url.addParam("text_plain_id", attach.plain);
    url.addParam("text_html_id", attach.html);
    url.addParam("category_id", attach.category_id);
    url.addParam("rename_text", attach.rename_text);
    url.addParam("mail_id", mail_id);
    url.requestUpdate("systemMsg", function() {
      UserEmail.listAttachLink(mail_id);
    });
  },

  deleteLinkAttachment : function(mail_id, link_id) {
    var url = new Url("messagerie", "ajax_do_unlink_attachment");
    url.addParam("link_id", link_id);
    url.requestUpdate("systemMsg", function() {
      UserEmail.listAttachLink(mail_id);
    });
  },

  /**
   * Display the view for adding attachments to a mail
   *
   * @param int mail_id The id of the mail
   */
  addAttachment: function(mail_id) {
    var url = new Url('messagerie', 'ajax_add_mail_attachment');
    url.addParam('mail_id', mail_id);
    url.requestModal(700, 300, {onClose: UserEmail.reloadAttachments.curry(mail_id)});
  },

  /**
   * Reload the attachments for the given mail
   *
   * @param int mail_id The id of the mail
   */
  reloadAttachments: function(mail_id) {
    var url = new Url('messagerie', 'ajax_reload_mail_attachments');
    url.addParam('mail_id', mail_id);
    url.requestUpdate('list_attachments');
  },

  /**
   * Refresh the counts for all the folders
   */
  refreshCounts: function() {
    var url = new Url('messagerie', 'ajax_refresh_counts_usermails');
    url.addParam('account_id', UserEmail.account_id);
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

  /**
   * Mark a folder as selected, and reload the list of mails
   *
   * @param int    account_id The account id
   * @param string folder     The name of the folder
   */
  selectFolder: function(account_id, folder, display_all) {
    var old_icon = $$('div.folder.selected i.folder-icon')[0];
    if (old_icon) {
      old_icon.removeClassName('fa-folder-open');
      old_icon.addClassName('fa-folder');
    }
    $$('div.folder.selected')[0].removeClassName('selected');
    $$('div.folder[data-folder=' + folder + ']')[0].addClassName('selected');
    var new_icon = $$('div.folder.selected i.folder-icon')[0];
    if (new_icon) {
      new_icon.removeClassName('fa-folder');
      new_icon.addClassName('fa-folder-open');
    }

    $$('ul.subfolders_list').invoke('hide');
    var li = $$('div.folder.selected ')[0].up('li');

    if (li.down('ul.subfolders_list')) {
      li.down('ul.subfolders_list').show();
    }

    if (li.hasClassName('subfolder')) {
      UserEmail.showFolderList(li);
    }

    if (display_all) {
      $('buttonfolderActions-' + folder).down('i.fa-eye').hide();
      $('buttonfolderActions-' + folder).down('i.fa-eye-slash').show();
    }
    else {
      $('buttonfolderActions-' + folder).down('i.fa-eye').show();
      $('buttonfolderActions-' + folder).down('i.fa-eye-slash').hide();
    }

    UserEmail.resetSearchFilters();

    if (display_all && getForm('searchOptions')) {
      $V(getForm('searchOptions').elements['query_range'], 'subfolders');
    }
    else if (!display_all && getForm('searchOptions')) {
      $V(getForm('searchOptions').elements['query_range'], 'selected');
    }

    UserEmail.refreshList(account_id, folder);
  },

  showFolderList: function(element) {
    if (element.up('ul.subfolders_list')) {
      element = element.up('ul.subfolders_list');
      element.show();
      if (element.up('li.subfolder')) {
        UserEmail.showFolderList(element.up('li.subfolder'));
      }
    }
  },

  editFolder: function(account_id, folder_id) {
    var url = new Url('messagerie', 'ajax_edit_mail_folder');
    url.addParam('account_id', account_id);
    url.addParam('folder_id', folder_id);
    url.requestModal(null, null, {
      onClose: UserEmail.refreshFolders.curry(account_id)
    });
  },

  refreshFolders: function(account_id) {
    var url = new Url('messagerie', 'ajax_list_folders');
    url.addParam('account_id', account_id);
    url.addParam('selected_folder', UserEmail.getSelectedFolder());
    url.requestUpdate('folders');
  },

  getSelectedFolder: function() {
    return $$('div.folder.selected')[0].readAttribute('data-folder');
  },
    print: function (mail_id) {
        var url = new Url('messagerie', 'ajax_print_mails');
        if (mail_id) {
            url.addParam('mail_ids', JSON.stringify([mail_id]))
        } else if (this.getSelectedMails() !== '[]') {
            url.addParam('mail_ids', this.getSelectedMails())
        }
        else {
            return;
        }
        url.popup(1000, 1000);
    }
};
