/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ContextualIntegration = {
  updateList: function(){
    var url = new Url("context", "ajax_list_integrations");
    url.requestUpdate("list-integrations");
  },
  create: function(){
    ContextualIntegration.edit(0);
  },
  edit: function(id){
    var url = new Url("context", "ajax_edit_integration");
    url.addNotNullParam("integration_id", id);
    url.requestUpdate("edit-integration");
  },
  editCallback: function(id){
    ContextualIntegration.edit(id);
    ContextualIntegration.updateList();
  },

  displayIcon: function(src) {
    if (!/^https?:\/\//.exec(src)) {
      return;
    }

    $('icon-url-container').down('img').src = src;
  },

  createLocation: function(integration_id){
    ContextualIntegration.editLocation(0, integration_id);
  },
  editLocation: function(id, integration_id){
    var url = new Url("context", "ajax_edit_integration_location");
    url.addNotNullParam("location_id", id);
    url.addNotNullParam("integration_id", integration_id);
    url.requestModal(400, 250);
  },
  editLocationCallback: function(id, obj){
    ContextualIntegration.edit(obj.integration_id);
  },
  insertPattern: function (pattern, url) {
    url.replaceInputSelection("%"+pattern+"%");
  },

  do_integration: function(element, unique_id){
    var mode = element.get("display_mode");
    var urlString = element.get("url");
    var title = element.get("title");
    var url = new Url();

    console.log(urlString);

    switch (mode) {
      case "modal":
        url.modal({width: "100%", height: "100%", baseUrl: urlString, title: title});
        break;

      case "popup":
        url.popup("100%", "100%", 'Appel contextuel ' + unique_id, null, null, urlString);
        break;

      case "current_tab":
        console.log('current');
        url.redirect(urlString);
        break;

      case "new_tab":
        url.open(urlString);
        break;
    }
  },

  /**
   * Autocomplete du champs icône
   *
   * @param form_name
   */
  iconAutocomplete: function (form_name) {
    let form = getForm(form_name);

    new Url("context", "icon_autocomplete")
      .autoComplete(form.elements.keywords, null, {
        minChars: 2,
        dropdown: true,
        width: '300px',
        afterUpdateElement: function (field, selected) {
          let url = selected.get("url");
          let name = selected.get("name");

          (url === "url") ? $V(field.form.icon_url, '') : $V(field.form.icon_url, url);
          $V(field.form.icon_name, name);
          $V(field.form.keywords, name);
          $("icon-url-container").parentElement.setVisible(url === "url");
        }
      });
  },

  /**
   * Vérification du champs URL
   *
   * @param form_name
   */
  toggleIconURL: function(form_name) {
    let form = getForm(form_name);

    $("icon-url-container").parentElement.setVisible($V(form.icon_name).indexOf("fa") === -1 && $V(form.icon_name).indexOf("URL") === 0);
  },
};
