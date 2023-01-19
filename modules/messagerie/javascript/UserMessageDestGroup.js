/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

UserMessageDestGroup = {
  filters_form: null,
  edit_form: null,

  edit: function(group_id) {
    new Url('messagerie', 'edit_user_message_dest_groups')
      .addParam('group_id', group_id)
      .requestModal(600, 600, {
        onClose: this.refreshList,
        method: 'post',
        getParameters: {m: 'messagerie', a: 'edit_user_message_dest_groups'}
      });
  },

  addUser: function(user_id, user_view) {
    var users = [];
    if ($V(this.edit_form.elements['added_users_id']) != '') {
      users = $V(this.edit_form.elements['added_users_id']).split('|');
    }

    users.push(user_id);
    $V(this.edit_form.elements['added_users_id'], users.join('|'));

    var div = DOM.div();
    div.insert(DOM.div({class: 'me-user-chips-content'}, user_view));
    div.insert(DOM.button({type: 'button', class: 'cancel me-tertiary notext', onclick: "UserMessageDestGroup.removeUser(" + user_id + ");"}, $T('Delete')));
    $('members-list').insert(DOM.div({id: 'CMediusers-' + user_id, class: 'me-user-chips me-margin-right-3', onmouseover: "ObjectTooltip.createEx(this, 'CMediusers-'" + user_id +");"}, div));
  },

  removeUser: function(user_id) {
    var users = $V(this.edit_form.elements['added_users_id']).split('|');
    users.splice(users.indexOf(user_id), 1);
    $V(this.edit_form.elements['added_users_id'], users.join('|'));
    $('CMediusers-' + user_id).remove();
  },

  removeUserLink: function(link_id) {
    var removed_links = [];
    if ($V(this.edit_form.elements['removed_links_id']) != '') {
      removed_links = $V(this.edit_form.elements['removed_links_id']).split('|');
    }
    removed_links.push(link_id);
    $V(this.edit_form.elements['removed_links_id'], removed_links.join('|'));
    $('CUserMessageDestGroupUserLink-' + link_id).remove();
  },

  filter: function() {
    new Url('messagerie', 'vw_user_message_dest_groups')
      .addParam('name_filter', $V(this.filters_form.elements['name_filter']))
      .addParam('user_filter', $V(this.filters_form.elements['user_filter']))
      .addParam('offset', 0)
      .addParam('refresh', 1)
      .requestUpdate('recipient_group_list', {
        method: 'post',
        getParameters: {m: 'messagerie', a: 'vw_user_message_dest_groups'}
      });

    return false;
  },

  changePage: function(offset) {
    new Url('messagerie', 'vw_user_message_dest_groups')
      .addParam('name_filter', $V(this.filters_form.elements['name_filter']))
      .addParam('user_filter', $V(this.filters_form.elements['user_filter']))
      .addParam('offset', offset)
      .addParam('refresh', 1)
      .requestUpdate('recipient_group_list', {
        method: 'post',
        getParameters: {m: 'messagerie', a: 'vw_user_message_dest_groups'}
      });
  },

  refreshList: function() {
    new Url('messagerie', 'vw_user_message_dest_groups')
      .addParam('offset', 0)
      .addParam('refresh', 1)
      .requestUpdate('recipient_group_list', {
        method: 'post',
        getParameters: {m: 'messagerie', a: 'vw_user_message_dest_groups'}
      });
  },

  toggleUsersList: function(group_guid, button) {
    $$('#' + group_guid + '-users-list span:nth-child(1n+8)').invoke('toggle');
    if (button.classList.contains('right')) {
      button.classList.remove('right');
      button.classList.add('down');
      button.classList.add('notext');
    }
    else {
      button.classList.remove('down');
      button.classList.remove('notext');
      button.classList.add('right');
    }
  }
};