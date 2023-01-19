/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

KerberosLDAP = window.KerberosLDAP || {
  edit: function (identifier_id, user_id, callback) {
    var url = new Url('admin', 'ajax_edit_kerberos_ldap_identifier');
    url.addParam('identifier_id', identifier_id);
    url.addParam('user_id', user_id);

    if (callback !== false) {
      callback = callback || {onClose: KerberosLDAP.showList.curry(user_id)};
    }

    url.requestModal(800, 400, callback);
  },

  submit: function (form, callback) {
    if (callback !== false) {
      callback = callback || {onComplete: Control.Modal.close};
    }

    return onSubmitFormAjax(form, callback);
  },

  confirmDeletion: function (form) {
    Modal.confirm(
      $T('CKerberosLdapIdentifier-confirm-Delete this object?'),
      {
        onOK: function () {
          $V(form.elements.del, '1');
          KerberosLDAP.submit(form, {onComplete: Control.Modal.close});
        }
      }
    );
  },

  showList: function (user_id) {
    var url = new Url('admin', 'ajax_show_kerberos_ldap_identifiers');
    url.addParam('user_id', user_id);
    url.requestUpdate('user-kerberos-security');
  },

  openImportModal: function () {
    var url = new Url('admin', 'vw_import_kerberos_identifiers');
    url.requestModal(800, 600);
  },

  uploadSaveUID: function (uid) {
    var uploadForm = getForm('upload-import-file-form');

    var url = new Url('admin', 'ajax_import_kerberos_identifiers');
    url.addParam('uid', uid);
    url.requestUpdate('import-steps');

    uploadForm.down('.upload-ok').show();
    uploadForm.down('.upload-error').hide();
  },
  uploadError:   function () {
    var uploadForm = getForm('upload-import-file-form');

    uploadForm.down('.upload-ok').hide();
    uploadForm.down('.upload-error').show();
  },
  uploadReset:   function () {
    var uploadForm = getForm('upload-import-file-form');

    uploadForm.down('.upload-ok').hide();
    uploadForm.down('.upload-error').hide();
  },
};