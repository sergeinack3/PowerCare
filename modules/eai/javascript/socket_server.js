/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function MLLP Server
 */
SocketServer = {
  action : function(port, type, process_id, uid, action){
    var url = new Url("eai", "ajax_socket_server_action");
    url.addParam("port", port);
    url.addParam("type", type);
    url.addParam("uid", uid);
    url.addParam("process_id", process_id);
    url.addParam("action", action);
    if (action == "stats" || action == "test") {
      url.requestUpdate("stats_"+uid);
      return;
    }
    url.requestUpdate("systemMsg", {
      onComplete: function() {
        document.location.reload();
      }
    });
  },
  
  trash : function(process_id, uid){
    var url = new Url("eai", "ajax_socket_server_trash");
    url.addParam("uid", uid);
    url.addParam("process_id", process_id);
    url.requestUpdate(uid);
  }
}