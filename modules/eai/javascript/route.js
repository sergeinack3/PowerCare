/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Route = {
  add : function (actor_guid, callback) {
    new Url("eai", "ajax_edit_route")
      .addParam("actor_guid", actor_guid)
      .requestModal(400)
      .modalObject.observe("afterClose", callback);
  },

  edit : function (id, callback) {
    new Url("eai", "ajax_edit_route")
      .addParam("route_id", id)
      .requestModal(400)
      .modalObject.observe("afterClose", callback);
  },

  refreshList : function () {
    new Url("eai", "ajax_list_route")
      .requestUpdate("list_route");
  },

  autocomplete_receiver : function () {
    var classe = "receiver";
    Route.autocomplete(classe);
  },

  autocomplete_sender : function () {
    var classe = "sender";
    Route.autocomplete(classe);
  },

  autocomplete: function (classe) {
    var form = getForm("editRoute");
    var classe_id_autocomplete = form.elements[classe + "_id_autocomplete"];

    new Url('eai', 'ajax_autocomplete')
      .addParam("input_field", classe_id_autocomplete.name)
      .autoComplete(classe_id_autocomplete, null, {
        minChars:      2,
        width:         "250px",
        method:        "get",
        dropdown:      true,
        callback:      function (input, queryString) {
          return queryString + "&object_class=" + $V(form.elements[classe + "_class"]);
        },
        updateElement: function (selected) {
          var classe_id = classe + "_id";
          $V(form[classe_id], selected.get('id'), false);
          $V(classe_id_autocomplete, selected.getText().trim(), false);
        }
      });
  }
};