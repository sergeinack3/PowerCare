/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

UserPermission = {
  loadList : function() {
    var url = new Url("admin", "ajax_search_users");
    url.requestUpdate('result_search_users');
  },

  editUser : function(user_id, tab_name) {
    var url = new Url("admin", "ajax_edit_user");
    url.addParam("user_id", user_id);
    url.addParam("tab_name", tab_name);
    url.requestModal("95%", "95%");
    url.modalObject.observe("afterClose", function() {
      UserPermission.loadList();
      store.remove('tabcheck');
    });
  },

  callback : function() {
    Control.Modal.close();
    UserPermission.loadList();
  },

  changePage : function(page) {
    $V(getForm('listFilterUser').page, page);
  },

  changeFilter: function(order, way) {
    var form = getForm('listFilterUser');
    $V(form.order_col, order);
    $V(form.order_way, way);
    form.onsubmit();
  },

  destroySession: function (session_id) {
    var url = new Url('admin', 'do_destroy_session', 'dosql');
    url.addParam('session_id', session_id);
    url.requestUpdate('systemMsg', {method: 'post', onComplete: function () {getForm('search-users-auth').onsubmit();} });
  }
};