/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportTools = {
  exportObject: function () {
    var form = getForm("export_group");
    var group_id = form.object_id.value;
    var url = new Url("dPetablissement", "exportObject", "raw");
    var function_select = $V('function_select');

    url.addParam('group_id', group_id);
    url.addParam('function_select', function_select);

    url.open();
  },

  emptyFieldGroup: function () {
    var form = getForm("export_group");
    form.object_id.value = "";
    form.object_view.value = "";
  },

  updateFunctionList: function (group_id) {
    var url = new Url('importTools', 'listFunctionsByGroup');
    url.addParam('group_id', group_id);

    url.requestUpdate('listFunctions');
  },

  executeRedirectImportExport: function (redirect, module) {
    var url = new Url(module, redirect, 'tab');
    url.open();
  }
};
