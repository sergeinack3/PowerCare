/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ListeChoix = {
  edit: function(liste_id) {
    Form.onSubmitComplete = Object.isUndefined(liste_id) ? ListeChoix.onSubmitComplete : Prototype.emptyFunction;

    new Url("compteRendu", "ajax_edit_liste_choix")
      .addParam("liste_id", liste_id)
      .requestModal("70%", "80%", {afterClose: ListeChoix.refreshList});
  },

  onSubmitComplete: function (guid, properties) {
    Control.Modal.close();
    var id = guid.split("-")[1];
    ListeChoix.edit(id);
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form);
  },

  onSubmitChoix: function(form) {
    return onSubmitFormAjax(form, ListeChoix.refreshChoix);
  },

  editChoix: function(button) {
    var td      = button.up("td");
    var td_edit = td.previous("td");

    button.hide();

    td.down("button.tick").show();
    td.down("button.cancel").show();

    var textarea = DOM.textarea({id: button.form.name + "_modify"}, td_edit.get("valeur"));

    td_edit.update(textarea);

    textarea.setResizable({autoSave: true, step: "font-size"});
  },

  valideChoix: function(button) {
    var td      = button.up("td");
    var td_edit = td.previous("td");
    var form    = button.form;

    $V(form._modify, $V(td_edit.down("textarea")));

    form.onsubmit();
  },

  cancelEditChoix: function(button) {
    var td      = button.up("td");
    var td_edit = td.previous("td");

    button.hide();

    td.down("button.edit").show();
    td.down("button.tick").hide();

    td_edit.update(td_edit.get("valeur"));
  },

  confirmDeletion: function(button) {
    var form = button.form;
    var options = {
      typeName: "liste de choix",
      objName: $V(form.nom)
    };

    var ajax = function() {
      Control.Modal.close();
    };
    
    confirmDeletion(form, options, ajax);    
  },

  filter: function() {
    ListeChoix.refreshList();
    return false;
  },

  refreshChoix: function() {
    var form = getForm("Add-Choix");
    new Url("compteRendu", "ajax_list_choix")
      .addElement(form.liste_id)
      .requestUpdate("list-choix", function() {
        getForm("Add-Choix").focusFirstElement();
    });
  },

  refreshList: function() {
    var form = getForm("Filter");
    new Url("compteRendu", "ajax_list_listes_choix")
      .addElement(form.user_id)
      .addElement(form.function_id)
      .requestUpdate("list-listes_choix");
  },
  
  importCSV: function(owner_guid) {
    new Url("compteRendu", "listes_choix_import_csv")
      .addParam("owner_guid", owner_guid)
      .pop(500, 400, "Import de listes de choix");
  },
  
  exportCSV: function(owner_guid, ids) {
    new Url("compteRendu", "listes_choix_export_csv", "raw")
      .addParam("owner_guid", owner_guid)
      .addParam("ids", ids)
      .open();
  }
};
