/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportUsers = {
  checkAllRadio: function(radio_class, type) {
    $$('input.' + radio_class).each(function(input) {
      if (input.value === type) {
        input.checked = 'checked';
      }
    })
  },

  getAllRadio: function(radio_class) {
    var collection = [];
    $$('input.' + radio_class).each(function(input) {
      if (input.checked) {
        collection.push(input.name + '|' + input.value);
      }
    });

    return collection;
  },

  submitCreateNewProfile: function() {
    var new_name = window.prompt($T('CUser-import-give-new-name'));

    new_name = new_name.trim();

    if (!new_name) {
      alert($T('common-error-Missing parameter: %s', $T('CUser-import-new-name')));
      return;
    }

    var form = getForm('import-existing-mediusers');

    $V(form.elements.new_name, new_name);

    var url = new Url('mediusers', 'do_import_new_profile', 'dosql');
    url.addFormData(form);
    url.requestUpdate('result-import-exist-profile', {method: 'post', onComplete: function() {Control.Modal.close();}});
  },

  submitUpdateProfile: function() {
    var perms_module = ImportUsers.getAllRadio('profile-import-use-perm-mod');
    var perms_module_view = ImportUsers.getAllRadio('profile-import-use-view');
    var perms_object = ImportUsers.getAllRadio('profile-import-use-perm-obj');
    var preferences = ImportUsers.getAllRadio('profile-import-use-prefs');
    var permissions_functionnal = ImportUsers.getAllRadio('profile-import-use-perms_functionnal');

    var form = getForm('import-existing-mediusers');

    var url = new Url('mediusers', 'do_import_existing_profile', 'dosql');
    url.addFormData(form);
    url.addParam('perms_module[]', perms_module);
    url.addParam('perms_module_view[]', perms_module_view);
    url.addParam('perms_object[]', perms_object);
    url.addParam('preferences[]', preferences);
    url.addParam('permissions_functionnal[]', permissions_functionnal);
    url.requestUpdate('result-import-exist-profile', {method: 'post', onComplete: function() {Control.Modal.close();}});
  },

  displayExistingUser: function (user_guid) {
    var url = new Url('mediusers', 'ajax_show_profile_compare');
    url.addParam('user_guid', user_guid);
    url.requestModal('70%', '80%');
  },

  importNewProfile: function (user_guid) {
    var user_id = user_guid.split('-');
    var form = getForm('import-existing-mediusers');

    var url = new Url('mediusers', 'do_import_new_profile', 'dosql');
    url.addParam('user_id', user_id[1]);
    url.addParam('profile', 1);

    if (form) {
      url.addFormData(form);
    }
    else {
      url.addParam('perms', 1);
      url.addParam('prefs', 1);
      url.addParam('perms_functionnal', 1);
      url.addParam('file_name', user_guid);
    }

    url.requestUpdate('result-import-exist-profile', {
        method: 'post',
        onComplete: function() {getForm('check-import-profiles').onsubmit();}
      }
    );
  },

  checkDirectory: function(input) {
    var url = new Url("patients", "ajax_check_export_dir");
    url.addParam("directory", $V(input));
    url.requestUpdate("directory-check");
  },

  searchColumn: function (input) {
    var rows = $$('.search-import-row');

    rows.invoke('show');

    var terms = $V(input);
    if (!terms) {
      return;
    }

    rows.invoke('hide');

    terms = terms.split(/\s+/);
    rows.each(function (e) {
      var search = e.down('.search');

      if (!search) {
        return;
      }

      terms.each(function (term) {
        if (search.getText().like(term)) {
          e.show();
        }
      });
    });
  }
};
