/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CTunnel = {
  status_color  : ["red", "limegreen"],

  ajax : {
    onComplete: Control.Modal.close
  },

  proxyAction : function(action, id) {
    var param = "";
    if (action == "setlog") {
      param = prompt("Saisissez le niveau de debug :");
    }
    new Url("eai", "ajax_result_proxy")
      .addParam("action", action)
      .addParam("idTunnel", id)
      .addParam("param", param)
      .requestUpdate("result_action");
  },

  editTunnel : function($id) {
    new Url("eai", "ajax_edit_tunnel")
      .addParam('tunnel_id', $id)
      .requestModal()
      .modalObject.observe("afterClose", CTunnel.refreshList);
  },

  refreshList : function () {
    new Url("eai", "ajax_refresh_list_tunnel")
      .requestUpdate("listTunnel");
  },

  submit : function(form) {
    return onSubmitFormAjax(form, this.ajax);
  },

  confirmDeletion : function(form, options) {

    confirmDeletion(form, options, this.ajax);
  },

  verifyAvaibility : function(element) {
    new Url("eai", "ajax_get_tunnel_status")
      .addParam("source_guid", element.get('guid'))
      .requestJSON(function(status) {
        var title = element.title;
        element.title = "";

        element.setStyle({color:CTunnel.status_color[status.reachable]});

        element.onmouseover = function() {
          ObjectTooltip.createDOM(element,
            DOM.div(null,
              DOM.table({className:"main tbl", style:"max-width:350px"},
                DOM.tr(null,
                  DOM.th(null, title)
                ),
                DOM.tr(null,
                  DOM.td({className:"text"},
                    DOM.strong(null, "Message : "), status.message)
                )
              )
            ).hide())
        };
      })
  }
};