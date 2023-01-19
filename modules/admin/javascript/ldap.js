/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

LDAPSource = window.LDAPSource || {
  list: function (dom_id) {
    var url = new Url('admin', 'ajax_list_ldap_sources');
    url.requestUpdate(dom_id || 'ldap-sources')
  },

  edit: function (source_id, group_id, callback) {
    var url = new Url('admin', 'edit_ldap_source');
    url.addParam('source_id', source_id);
    url.addParam('group_id', group_id);

    callback = callback || {
      onClose: function () {
        LDAPSource.list();
      }
    };

    url.requestModal(800, 600, callback);
  },

  test: function (source_id) {
    var url = new Url('admin', 'test_ldap_source');
    url.addParam('source_id', source_id);
    url.requestModal(800, 600, {title: $T('utilities-source-ldap')});
  },

  submit: function (form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  confirmDeletion: function (form) {
    Modal.confirm(
      $T('CSourceLDAP-confirm-Delete this object?'),
      {
        onOK: function () {
          $V(form.elements.del, '1');
          LDAPSource.submit(form);
          $V(form.elements.del, '0');
        }
      }
    );
  },

  bind: function (source_id, rdn, pwd) {
    var url = new Url('admin', 'ajax_tests_ldap');
    url.addParam('source_ldap_id', source_id);
    url.addParam('ldaprdn', $V(rdn));
    url.addParam('ldappass', $V(pwd));
    url.requestUpdate('test-ldap-bind-' + source_id);
  },

  search: function (source_id, rdn, pwd, filter, attr) {
    var url = new Url('admin', 'ajax_tests_ldap');
    url.addParam('source_ldap_id', source_id);
    url.addParam('action', "search");
    url.addParam('ldaprdn', $V(rdn));
    url.addParam('ldappass', $V(pwd));
    url.addParam('filter', $V(filter));
    url.addParam('attributes', $V(attr));
    url.requestUpdate('test-ldap-search-' + source_id);
  }
};