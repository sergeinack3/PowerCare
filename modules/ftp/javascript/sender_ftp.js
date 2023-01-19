/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Sender FTP EAI
 */
SenderFTP = {
  actor_guid: null,
  
  dispatch: function(actor_guid) {
    new Url("ftp", "ajax_dispatch_files_ftp")
      .addParam("actor_guid", actor_guid)
      .addParam("trace", 1)
      .requestModal("60%");
  },
  
  readFilesSenders: function() {
    var url = new Url("ftp", "ajax_read_ftp_files");
    url.requestUpdate("CSenderFTP-utilities_read-files-senders");
  }
};
