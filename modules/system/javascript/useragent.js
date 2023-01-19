/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

UserAgent = window.UserAgent = {
  edit: function (id) {
    var url = new Url("system", "ajax_edit_user_agent");
    url.addParam("user_agent_id", id);
    url.requestModal(600, 350, {
      onClose: function () {
        UserAgent.refreshUALine(id);
      }
    });
  },

  openAuthentications: function (id) {
    var url = new Url("system", "vw_user_authentications");
    url.addParam("user_agent_id", id);
    url.requestModal(900, 700);
  },

  refreshUALine: function (id) {
    var url = new Url("system", "ajax_refresh_user_agent");
    url.addParam("user_agent_id", id);
    url.requestUpdate("user_agent_" + id);
  },

  updateName: function (select, field) {
    var form = select.form;
    $V(form[field], $V(select));
    select.selectedIndex = 0;
  },

  changePage: function (start) {
    var form = getForm("filter_graph");
    $V(form.elements.start, start);
    form.onsubmit();
  },

  updateNameFromDetection: function(value, field, button){
    $V(button.form[field], value);
    button.style = "";
  }
};
