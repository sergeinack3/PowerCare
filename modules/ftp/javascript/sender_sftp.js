/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Sender FTP EAI
 */
SenderSFTP = {
  actor_guid: null,

  dispatch: function (actor_guid) {
    new Url("ftp", "ajax_dispatch_files_sftp")
      .addParam("actor_guid", actor_guid)
      .addParam("trace", 1)
      .requestModal("60%");
  }
};
