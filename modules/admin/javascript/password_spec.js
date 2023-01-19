/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PasswordSpec = {
  config: {},

  props: {
    weak:   null,
    strong: null,
    ldap:   null,
    admin:  null,
  },

  fields: {
    username:  null,
    type:      null,
    remote:    null,
    password:  null,
    password2: null,
  },

  values: {
    isLDAP:   false,
    username: null,
    type:     null,
    remote:   null,
  },

  rngButton: null,

  init: function (configuration, weak_prop, strong_prop, ldap_prop, admin_prop) {
    PasswordSpec.config = configuration;
    PasswordSpec.props.weak = weak_prop;
    PasswordSpec.props.strong = strong_prop;
    PasswordSpec.props.ldap = ldap_prop;
    PasswordSpec.props.admin = admin_prop;
  },

  registerUsernameField: function (element) {
    PasswordSpec.fields.username = element;
  },

  registerTypeField: function (element) {
    PasswordSpec.fields.type = element;
  },

  registerRemoteField: function (element) {
    PasswordSpec.fields.remote = element;
  },

  registerPasswordField: function (element) {
    PasswordSpec.fields.password = element;
  },

  registerPassword2Field: function (element) {
    PasswordSpec.fields.password2 = element;
  },

  registerRandomPasswordGeneratorButton: function (button) {
    PasswordSpec.rngButton = button;
  },

  getFieldValue: function (field) {
    if (PasswordSpec.fields[field]) {
      return $V(PasswordSpec.fields[field]);
    }

    return (PasswordSpec.values[field]) ?? null;
  },

  observe: function () {
    $H(PasswordSpec.fields).each(function (pair) {
      // Field not registered
      if (pair.value === null) {
        return;
      }

      if (pair.value instanceof RadioNodeList) {
        $A(pair.value).invoke('observe', 'change', function (event) {
          PasswordSpec.check();
        });
      } else {
        pair.value.observe('change', function (event) {
          PasswordSpec.check();
        });
      }
    });
  },

  check: function () {
    if (PasswordSpec.values.isLDAP) {
      return PasswordSpec.applyPasswordProp('ldap', PasswordSpec.props.ldap);
    }

    if (!PasswordSpec.config.strong_password) {
      return PasswordSpec.applyPasswordProp('weak', PasswordSpec.props.weak);
    }

    var type = PasswordSpec.getFieldValue('type');
    var isAdmin = (type == 1);

    if (PasswordSpec.config.admin_specific && isAdmin) {
      return PasswordSpec.applyPasswordProp('admin', PasswordSpec.props.admin);
    }

    if (PasswordSpec.config.apply_all_users) {
      return PasswordSpec.applyPasswordProp('strong', PasswordSpec.props.strong);
    }

    var remote = PasswordSpec.getFieldValue('remote');

    // Remote field is REVERTED
    var hasRemoteAccess = (remote != 1);

    if (hasRemoteAccess) {
      return PasswordSpec.applyPasswordProp('strong', PasswordSpec.props.strong);
    }

    return PasswordSpec.applyPasswordProp('weak', PasswordSpec.props.weak);
  },

  applyPasswordProp: function (spec, prop) {
    PasswordSpec.fields.password.className = prop;
    checkFormElement(PasswordSpec.fields.password);

    if (PasswordSpec.fields.password2) {
      PasswordSpec.fields.password2.className = prop;
    }

    if (PasswordSpec.rngButton) {
      PasswordSpec.rngButton.setAttribute('data-pwd-spec', spec);
    }
  }
};
