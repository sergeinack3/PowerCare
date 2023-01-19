/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Sender FS
 */
SenderFS = {
  actor_guid: null,
  
  dispatch: function(actor_guid) {
    new Url("system", "ajax_dispatch_files")
      .addParam("actor_guid", actor_guid)
      .addParam("trace"     , 1)
      .requestModal("60%");
  },
  
  createExchanges: function(actor_guid) {
    new Url("system", "ajax_dispatch_files")
      .addParam("actor_guid"  , actor_guid)
      .addParam("to_treatment", 0)
      .requestModal("60%");
  }
};