/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CMediuserFunctions = window.CMediuserFunctions || {
  currentUserId: null,
  loadView: function(userId) {
    this.currentUserId = userId;
    this.refreshView();
  },
  refreshView: function() {
    new Url("mediusers", "functions")
      .addParam("user_id", this.currentUserId)
      .requestUpdate("fonctions");
  },
  loadAddForm: function() {
    this.addFunctionRequest(null, null, null);
  },
  displayLegend: function() {
    Modal.open("mediuser_function_legend", {
      showClose: true,
      title: $T('Legend')
    });
  },
  editPermission: function(permId, permValue, title) {
    var form = getForm('edit_perm_form');
    $V(form.perm_object_id, permId);
    $V(form.permission, permValue);
    Modal.open('edit_perm', {title: title, showClose: true, height: 100, width: 260} );
  },
  addPermission: function(objectId, objectClass) {
    var form = getForm('add_perm_form');
    $V(form.object_id, objectId);
    $V(form.object_class, objectClass);
    form.onsubmit();
  },
  deleteFunction: function(secondaryFunctionId, secondaryFunctionName, permId) {
    var form = getForm('edit_fun_form');
    $V(form.function_id, '');
    $V(form.secondary_function_id, secondaryFunctionId);
    return confirmDeletion(
      form,
      {typeName: $T('CMediuser-Functions the function'), objName: secondaryFunctionName, ajax: 1},
      function() {
        if (permId) {
          this.deletePerm(permId, false);
        }
        else {
          this.refreshView();
        }
      }.bind(this)
    );
  },
  deletePerm: function(permId, confirm) {
    var form = getForm('edit_perm_form');
    if (confirm) {
      return confirmDeletion(
        form,
        {typeName: $T('CMediuser-Functions the perm')},
        function() { Control.Modal.close(); this.refreshView();}.bind(this)
      )
    }
    else {
      $V(form.perm_object_id, permId);
      $V(form.del, 1);
      return onSubmitFormAjax(form, function() { this.refreshView(); }.bind(this));
    }
  },
  updateFunction: function(functionId, functionLib, userLib) {
    if (confirm($T('CMediuser-Functions confirm function creation', functionLib, userLib))) {
      var form = getForm('edit_fun_form');
      $V(form.secondary_function_id, '');
      $V(form.function_id, functionId);
      form.onsubmit();
    }
  },
  upgradeFunction: function(functionId, secondaryFunctionId) {
    if (confirm($T('CMediuser-Functions confirm upgrade'))) {
      var form = getForm('upgrade_fun_form');
      $V(form.function_id, functionId);
      $V(getForm('downgrade_fun_form').secondary_function_id, secondaryFunctionId);
      form.onsubmit();
    }
  },
  reloadAddFunctionWithGroup: function(groupId) {
    Control.Modal.close();
    this.addFunctionRequest(groupId, function() {
      this.changeAddPermValues(groupId, 'CGroups');
    }.bind(this));
  },
  addFunctionRequest: function(groupId, callback) {
    new Url("mediusers", "function_add")
      .addNotNullParam("group_id", groupId)
      .addParam("user_id", this.currentUserId)
      .requestModal(300, null, {
        onComplete: callback
      });
  },
  changeAddPermValues: function(objectId, objectClass) {
    if (objectId === '') {
      return false;
    }
    var permissionForm = getForm('addMediuserPermission');
    $V(permissionForm.object_id   , objectId);
    $V(permissionForm.object_class, objectClass);
  },
  onPermFormSubmit: function(form) {
    if ($V(form.permission)) {
      return onSubmitFormAjax(form, function() {
        Control.Modal.close();
        this.refreshView();
      }.bind(this));
    }
    else {
      Control.Modal.close();
      this.refreshView();
      return false;
    }
  },
  onAddSecFunctSubmit: function(form) {
    var permissionForm = getForm('addMediuserPermission');
    if ($V(form.function_id) === "") {
      return permissionForm.onsubmit();
    }
    else {
      return onSubmitFormAjax(form, function() {
        permissionForm.onsubmit();
      });
    }
  }
};
