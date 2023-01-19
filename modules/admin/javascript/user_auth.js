/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

UserAuth = {
  makeUserAutocomplete: function (form, input_field) {
    var user_autocomplete = new Url("mediusers", "ajax_users_autocomplete");
    user_autocomplete.addParam('input_field', input_field.name);
    user_autocomplete.addParam("edit", 0);

    user_autocomplete.autoComplete(input_field, null, {
      minChars:           0,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        if ($V(input_field) == "") {
          $V(input_field, selected.down('.view').innerHTML);
        }

        var id = selected.getAttribute("id").split("-")[2];
        $V(form.elements.user_id, id, true);
      }
    });
  },

  submitAuthFilter: function(form) {
    $V(form.type, 'success');
    Url.update(form, 'users-auth-results-success');
  
    $V(form.type, 'error');
    Url.update(form, 'users-auth-results-error');
  
    return false;
  },

  changePageUserAuth: function (type, start) {
    var form = getForm('search-users-auth');
    $V(form.elements.start, start);
    
    $V(form.type, type);
    Url.update(form, 'users-auth-results-'+type);
    
    $V(form.elements.start, 0);
  },

  destroySession: function (session_id) {
    var url = new Url('admin', 'do_destroy_session', 'dosql');
    url.addParam('session_id', session_id);
    url.requestUpdate('systemMsg', {method: 'post', onComplete: function () {Control.Modal.close(); getForm('search-users-auth').onsubmit();} });
  },

  edit: function(auth_id) {
    new Url('admin', 'vw_user_authentication')
      .addParam('auth_id', auth_id)
      .requestModal(600);
  },

  updateExpirationDateFilter: function(session_state_input) {
    var form = session_state_input.form;

    switch ($V(session_state_input)) {
      case 'all':
        Calendar.clear(form.elements._expiration_start_date);
        Calendar.clear(form.elements._expiration_end_date);
        break;

      case 'active':
        Calendar.setNow(form.elements._expiration_start_date);
        Calendar.clear(form.elements._expiration_end_date);
        break;

      case 'expired':
        Calendar.clear(form.elements._expiration_start_date);
        Calendar.setNow(form.elements._expiration_end_date);
        break;

      default:
    }
  },

  purgeUserAuthentication: function(user_id, error = 0) {
    const url = new Url("admin", "do_user_authentication_purge", "dosql");
    url.addParam("user_id", user_id);
    url.addParam('error', error);
    url.requestUpdate(SystemMessage.id, {method: "post"});
  },

  updateAfterPurge: function (count, error, user_id) {
    const type = (error === '1' ? 'errors' : 'success');
    $$('span.auth-count-' + type).each(function (elt) {
      const old_count = elt.get('count');
      let new_count = old_count - count;

      if (new_count < 0) {
        new_count = 0;
      }

      elt.innerHTML = new_count.toLocaleString('fr-FR');
      elt.set('count', new_count);

      const container_id = (error === '1') ? 'tab-errors' : 'tab-success';
      UserAuth.reloadAuthList(container_id, user_id);
    });
  },

  reloadAuthList: function (container_id, user_id) {
    const action = container_id === 'tab-success' ? 'ajax_vw_user_authentications_success' : 'ajax_vw_user_authentications_errors';
    const total = $('span-' + container_id).get('count');

    const url = new Url('admin', action);
    url.addParam('total', total);
    url.addParam('user_id', user_id);
    url.requestUpdate(container_id);
  },

  showUsersAuthStats: function () {
    new Url('admin', 'vw_users_auth_stats').requestModal('90%', '90%');
  },
};
